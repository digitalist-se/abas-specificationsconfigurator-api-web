<?php

namespace Tests\Unit\CRM;

use App\CRM\Enums\SalesforceContentDocumentLinkVisibility;
use App\CRM\Enums\SalesforceLeadProductFamily;
use App\CRM\Enums\SalesforceLeadSource;
use App\CRM\Enums\SalesforceLeadStatus;
use App\CRM\Enums\SalesforceObjectType;
use App\CRM\Enums\SalesforceTaskPriority;
use App\CRM\Enums\SalesforceTaskStatus;
use App\CRM\Enums\SalesforceTaskSubject;
use App\CRM\Service\SalesforceCRMService;
use App\Enums\ContactType;
use App\Enums\EventType;
use App\Events\ExportedDocument;
use App\Http\Resources\SpecificationDocument;
use App\Models\User;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\Commands\Salesforce\Action;
use Tests\TestCase;
use Webmozart\Assert\Assert;

class SalesforceCRMServiceTest extends TestCase
{
    use WithFaker;

    private $actionProtocol;

    /**
     * @param string $token
     *
     * @return PromiseInterface
     */
    private function mockResponseAuthToken(?string $token = null): PromiseInterface
    {
        $token ??= $this->faker->sha1;

        return Http::response([
            'access_token' => $token,
            'signature'    => $this->faker->sha1,
            'scope'        => 'openid id api',
            'token_type'   => 'Bearer',
        ]);
    }

    private function mockResponseCreate(?string $id = null): PromiseInterface
    {
        return Http::response(['Id' => $id ?? $this->faker()->uuid]);
    }

    private function mockResponseSearch(?array $records = []): PromiseInterface
    {
        return Http::response(['records' => $records]);
    }

    private function mockResponseGet(string $id, SalesforceObjectType $objectType, array $attributes): PromiseInterface
    {
        $data = [
            'Id'         => $id,
            'attributes' => [
                'type' => 'Lead',
                'url'  => '/services/data/vXX.X/sobjects/'.$objectType->value.'/'.$id,
            ],
        ];

        return Http::response(array_merge($data, $attributes));
    }

    private function mockResponseUpdate(string $id): PromiseInterface
    {
        return Http::response(null, 204);
    }

    public function assertActionProtocol(): self
    {
        $this->assertTrue(
            collect($this->actionProtocol)->every(fn ($done) => $done),
            'Not all expected actions were performed: '.json_encode($this->actionProtocol, JSON_PRETTY_PRINT)
        );

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.salesforce.enabled', true);
    }

    private function contactTypeDataProvider(): array
    {
        return [
            'user'    => [ContactType::User],
            'company' => [ContactType::Company],
        ];
    }

    /**
     * @test
     */
    public function it_authorizes_requests()
    {
        $token = $this->faker->sha1;
        Http::fake([
            '*' => Http::sequence([
                $this->mockResponseAuthToken($token),
                $this->mockResponseSearch(),
            ]),
        ]);

        $service = $this->app->make(SalesforceCRMService::class);
        $service->searchLeadBy($this->faker->email(), SalesforceLeadStatus::PreLead);

        Http::assertSent(function (?Request $request, ?Response $response) use ($token) {
            if ('/services/oauth2/token' === $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertNotNull($request);
            $authHeader = $request->header('Authorization')[0] ?? null;
            $this->assertEquals("Bearer {$token}", $authHeader);

            return true;
        });
    }

    private function setObjectAction(bool $done, SalesforceObjectType $objectType, Action $action): self
    {
        $key = sprintf('%s.%s', $objectType->value, $action->value);

        $this->actionProtocol[$key] = $done;

        return $this;
    }

    /**
     * @param array<array{SalesforceObjectType, Action, ?SalesforceObjectType}> $expectations
     *
     * @return self
     */
    private function setActionProtocolExpectations(array $expectations): self
    {
        $this->actionProtocol = [];

        foreach ($expectations as $expectation) {
            $this->setObjectAction(false, $expectation[0], $expectation[1]);
        }

        return $this;
    }

    /**
     * @test
     */
    public function it_can_track_user_registered()
    {
        $contactType = ContactType::User;
        $user = $this->givenIsAUserWithContactData($contactType);
        $service = $this->app->make(SalesforceCRMService::class);
        $leadId = $this->faker->uuid;
        $ownerId = $this->faker->uuid;
        $taskId = $this->faker->uuid;

        $expectations = [
            [SalesforceObjectType::Contact, Action::Search],
            [SalesforceObjectType::Lead, Action::Search],
            [SalesforceObjectType::Lead, Action::Create],
            [SalesforceObjectType::Lead, Action::Get],
            [SalesforceObjectType::Task, Action::Create],
        ];

        $this->setActionProtocolExpectations($expectations);

        Http::fake([
            '*' => Http::sequence([
                $this->mockResponseAuthToken(), // Auth
                $this->mockResponseSearch(), // Search Contact by Email
                $this->mockResponseSearch(), // Search Lead by Email
                $this->mockResponseCreate($leadId), // Create Lead
                $this->mockResponseGet($leadId, SalesforceObjectType::Lead, ['OwnerId' => $ownerId]), // Get Lead
                $this->mockResponseCreate($taskId), // Create Task
            ]),
        ]);

        $service->handleUserRegistered(new Registered($user));

        $this
            ->assertRequestContactSearch($user, $contactType)
            ->assertRequestLeadSearch($user, $contactType)
            ->assertRequestLeadCreation($user, $contactType)
            ->assertRequestLeadGet($leadId)
            ->assertRequestTaskCreationForUserRegistered($leadId, $ownerId)
            ->assertActionProtocol()
            ->assertSalesforceId($user, $expectations);
    }

    /**
     * @test
     */
    public function it_can_track_document_export(): void
    {
        $contactType = ContactType::Company;
        $user = $this->givenIsAUserWithContactData($contactType);
        $document = $this->givenIsASpecificationDocument($user);
        $service = $this->app->make(SalesforceCRMService::class);
        $leadId = $this->faker->uuid;
        $ownerId = $this->faker->uuid;
        $taskId = $this->faker->uuid;
        $contentVersionId = $this->faker->uuid;
        $contentDocumentId = $this->faker->uuid;
        $contentDocumentLinkId = $this->faker->uuid;

        $expectations = [
            [SalesforceObjectType::Contact, Action::Search],
            [SalesforceObjectType::Lead, Action::Search],
            [SalesforceObjectType::Lead, Action::Create],
            [SalesforceObjectType::Lead, Action::Get],
            [SalesforceObjectType::Task, Action::Search],
            [SalesforceObjectType::Task, Action::Create],
            [SalesforceObjectType::ContentVersion, Action::Create],
            [SalesforceObjectType::ContentVersion, Action::Search, SalesforceObjectType::ContentDocument],
            [SalesforceObjectType::ContentDocumentLink, Action::Create],
        ];

        $this->setActionProtocolExpectations($expectations);

        Http::fake([
            '*' => Http::sequence([
                $this->mockResponseAuthToken(), // Auth
                $this->mockResponseSearch(), // Search Contact by Email
                $this->mockResponseSearch(), // Search Lead by Email
                $this->mockResponseCreate($leadId), // Create Lead
                $this->mockResponseGet($leadId, SalesforceObjectType::Lead, ['OwnerId' => $ownerId]), // Get Lead
                $this->mockResponseSearch(), // Search Task by Subject and WhoId
                $this->mockResponseCreate($taskId), // Create Task
                $this->mockResponseCreate($contentVersionId), // Create ContentVersion
                $this->mockResponseSearch([['ContentDocumentId' => $contentDocumentId]]), // Search ContentVersion
                $this->mockResponseCreate($contentDocumentLinkId), // Create $contentDocumentLink
            ]),
        ]);

        $service->handleDocumentExport(new ExportedDocument($user, $document));

        $this
            ->assertRequestContactSearch($user, $contactType)
            ->assertRequestLeadSearch($user, $contactType)
            ->assertRequestLeadCreation($user, $contactType)
            ->assertRequestLeadGet($leadId)
            ->assertRequestTaskSearch($leadId)
            ->assertRequestTaskCreationForDocumentExport($leadId, $ownerId)
            ->assertRequestContentVersionCreation()
            ->assertRequestContentVersionSearch($contentVersionId)
            ->assertRequestContentDocumentLinkCreation($taskId, $contentDocumentId)
            ->assertActionProtocol()
            ->assertSalesforceId($user, $expectations);

        @unlink($document->outputExcelFilename());
        @unlink($document->outputZipFilename());
    }

    /**
     * @test
     */
    public function it_can_track_user_registered_and_updating_existing_lead()
    {
        $contactType = ContactType::User;
        $user = $this->givenIsAUserWithContactData($contactType);
        $service = $this->app->make(SalesforceCRMService::class);
        $leadId = $this->faker->uuid;
        $ownerId = $this->faker->uuid;
        $taskId = $this->faker->uuid;

        $expectations = [
            [SalesforceObjectType::Contact, Action::Search],
            [SalesforceObjectType::Lead, Action::Search],
            [SalesforceObjectType::Lead, Action::Update],
            [SalesforceObjectType::Lead, Action::Get],
            [SalesforceObjectType::Task, Action::Create],
        ];

        $this->setActionProtocolExpectations($expectations);

        Http::fake([
            '*' => Http::sequence([
                $this->mockResponseAuthToken(), // Auth
                $this->mockResponseSearch(), // Search Contact by Email
                $this->mockResponseSearch([['Id' => $leadId]]), // Search Lead by Email
                $this->mockResponseUpdate($leadId), // Update Lead
                $this->mockResponseGet($leadId, SalesforceObjectType::Lead, ['OwnerId' => $ownerId]), // Get Contact
                $this->mockResponseCreate($taskId), // Create Task
            ]),
        ]);

        $service->handleUserRegistered(new Registered($user));

        $this
            ->assertRequestContactSearch($user, $contactType)
            ->assertRequestLeadSearch($user, $contactType)
            ->assertRequestLeadUpdate($leadId, $user, $contactType)
            ->assertRequestLeadGet($leadId)
            ->assertRequestTaskCreationForUserRegistered($leadId, $ownerId)
            ->assertActionProtocol()
            ->assertSalesforceId($user, $expectations);
    }

    /**
     * @test
     */
    public function it_can_track_user_registered_and_updating_existing_contact()
    {
        $contactType = ContactType::User;
        $user = $this->givenIsAUserWithContactData($contactType);
        $service = $this->app->make(SalesforceCRMService::class);
        $contactId = $this->faker->uuid;
        $ownerId = $this->faker->uuid;
        $taskId = $this->faker->uuid;

        $expectations = [
            [SalesforceObjectType::Contact, Action::Search],
            [SalesforceObjectType::Contact, Action::Update],
            [SalesforceObjectType::Contact, Action::Get],
            [SalesforceObjectType::Task, Action::Create],
        ];

        $this->setActionProtocolExpectations($expectations);

        Http::fake([
            '*' => Http::sequence([
                $this->mockResponseAuthToken(), // Auth
                $this->mockResponseSearch([['Id' => $contactId]]), // Search Contact by Email
                $this->mockResponseUpdate($contactId), // Update Contact
                $this->mockResponseGet($contactId, SalesforceObjectType::Contact, ['OwnerId' => $ownerId]), // Get Contact
                $this->mockResponseCreate($taskId), // Create Task
            ]),
        ]);

        $service->handleUserRegistered(new Registered($user));

        $this
            ->assertRequestContactSearch($user, $contactType)
            ->assertRequestContactUpdate($contactId, $user, $contactType)
            ->assertRequestContactGet($contactId)
            ->assertRequestTaskCreationForUserRegistered($contactId, $ownerId)
            ->assertActionProtocol()
            ->assertSalesforceId($user, $expectations);
    }

    /**
     * @test
     */
    public function it_can_track_document_export_and_update_existing_task(): void
    {
        $contactType = ContactType::Company;
        $user = $this->givenIsAUserWithContactData($contactType);
        $document = $this->givenIsASpecificationDocument($user);
        $service = $this->app->make(SalesforceCRMService::class);
        $leadId = $this->faker->uuid;
        $ownerId = $this->faker->uuid;
        $taskId = $this->faker->uuid;
        $contentVersionId = $this->faker->uuid;
        $contentDocumentId = $this->faker->uuid;
        $contentDocumentLinkId = $this->faker->uuid;

        $expectations = [
            [SalesforceObjectType::Contact, Action::Search],
            [SalesforceObjectType::Lead, Action::Search],
            [SalesforceObjectType::Lead, Action::Create],
            [SalesforceObjectType::Lead, Action::Get],
            [SalesforceObjectType::Task, Action::Search],
            [SalesforceObjectType::Task, Action::Update],
            [SalesforceObjectType::ContentVersion, Action::Create],
            [SalesforceObjectType::ContentVersion, Action::Search],
            [SalesforceObjectType::ContentDocumentLink, Action::Create],
        ];

        $this->setActionProtocolExpectations($expectations);

        Http::fake([
            '*' => Http::sequence([
                $this->mockResponseAuthToken(), // Auth
                $this->mockResponseSearch(), // Search Contact by Email
                $this->mockResponseSearch(), // Search Lead by Email
                $this->mockResponseCreate($leadId), // Create Lead
                $this->mockResponseGet($leadId, SalesforceObjectType::Lead, ['OwnerId' => $ownerId]), // Get Lead
                $this->mockResponseSearch([['Id' => $taskId]]), // Search Task by Subject and WhoId
                $this->mockResponseUpdate($taskId), // Update Task
                $this->mockResponseCreate($contentVersionId), // Create ContentVersion
                $this->mockResponseSearch([['ContentDocumentId' => $contentDocumentId]]), // Search ContentVersion
                $this->mockResponseCreate($contentDocumentLinkId), // Create $contentDocumentLink
            ]),
        ]);

        $service->handleDocumentExport(new ExportedDocument($user, $document));

        $this
            ->assertRequestContactSearch($user, $contactType)
            ->assertRequestLeadSearch($user, $contactType)
            ->assertRequestLeadCreation($user, $contactType)
            ->assertRequestLeadGet($leadId)
            ->assertRequestTaskSearch($leadId)
            ->assertRequestTaskUpdateForDocumentExport($taskId, $leadId, $ownerId)
            ->assertRequestContentVersionCreation()
            ->assertRequestContentVersionSearch($contentVersionId)
            ->assertRequestContentDocumentLinkCreation($taskId, $contentDocumentId)
            ->assertActionProtocol()
            ->assertSalesforceId($user, $expectations);

        @unlink($document->outputExcelFilename());
        @unlink($document->outputZipFilename());
    }

    private function assertSalesforceId(User $user, array $expectations): self
    {
        $salesforce = $user->salesforce;
        foreach ($expectations as $expectation) {
            [$objectType, $action] = $expectation;
            $forcedType = $expectation[2] ?? null;
            if ($action === Action::Search && $forcedType === null) {
                continue;
            }

            if ($forcedType !== null) {
                $objectType = $forcedType;
            }

            $id = $salesforce->objectId($objectType);
            $this->assertNotEmpty($id, "Salesforce ID for {$objectType->value} should not be empty after {$action->value}");
        }

        return $this;
    }

    private function assertRequestSearch(SalesforceObjectType $objectType, string $needle): self
    {
        Http::assertSent(function (?Request $request) use ($objectType, $needle) {
            if ($request === null) {
                return false;
            }
            $path = $request->toPsrRequest()->getUri()->getPath();
            if ($path !== '/services/data/v63.0/query') {
                return false;
            }

            $query = urldecode($request->toPsrRequest()->getUri()->getQuery());
            if (! str_contains($query, 'FROM '.$objectType->value)) {
                return false;
            }

            $this->setObjectAction(true, $objectType, Action::Search);

            $this->assertStringContainsString($needle, $query);

            return true;
        });

        return $this;
    }

    private function assertRequestLeadSearch(User $user, ContactType $contactType) : self
    {
        return $this->assertRequestSearch(
            SalesforceObjectType::Lead,
            sprintf("WHERE Email = '%s'", $user->getContactEmail($contactType))
        );
    }

    private function assertRequestContactSearch(User $user, ContactType $contactType) : self
    {
        return $this->assertRequestSearch(
            SalesforceObjectType::Contact,
            sprintf("WHERE Email = '%s'", $user->getContactEmail($contactType))
        );
    }

    private function assertRequestTaskSearch($leadId) : self
    {
        return $this->assertRequestSearch(
            SalesforceObjectType::Task,
            sprintf("WHERE WhoId = '%s'", $leadId)
        );
    }

    private function assertRequestContentVersionSearch($contentVersionId) : self
    {
        return $this->assertRequestSearch(
            SalesforceObjectType::ContentVersion,
            sprintf("WHERE Id = '%s'", $contentVersionId)
        );
    }

    private function assertRequest(SalesforceObjectType $objectType, Action $action, ?string $id, callable $assert): self
    {
        $expectedPath = sprintf('/services/data/v63.0/sobjects/%s', $objectType->value);

        [$expectedMethod, $needsId] = match ($action) {
            Action::Create => ['POST', false],
            Action::Get    => ['GET', true],
            Action::Update => ['PATCH', true],
            default        => throw new InvalidArgumentException("Unsupported action: {$action->value}"),
        };

        if ($needsId) {
            Assert::notEmpty($id, 'ID is required for Get and Update actions');
            $expectedPath .= "/{$id}";
        }

        Http::assertSent(function (?Request $request) use ($objectType, $action, $expectedPath, $expectedMethod, $assert) {
            if ($request === null) {
                return false;
            }

            $path = $request->toPsrRequest()->getUri()->getPath();
            $method = $request->toPsrRequest()->getMethod();
            if (! ($expectedPath === $path && $expectedMethod === $method)) {
                return false;
            }

            $this->setObjectAction(true, $objectType, $action);

            $assert($request);

            return true;
        });

        return $this;
    }

    private function assertRequestLeadCreation($user, $contactType): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Lead,
            Action::Create,
            null,
            function (Request $request) use ($user, $contactType) {
                $data = $request->data();
                $this->assertEquals($user->getContactEmail($contactType), $data['Email']);
                $this->assertEquals($user->getContactFirstName($contactType), $data['FirstName']);
                $this->assertEquals($user->getContactLastName($contactType), $data['LastName']);
                $this->assertEquals($user->getContactCompany($contactType), $data['Company']);
                $this->assertEquals(SalesforceLeadSource::ERPPlanner->value, $data['LeadSource']);
                $this->assertEquals(SalesforceLeadStatus::PreLead->value, $data['Status']);
                $this->assertEquals(SalesforceLeadProductFamily::ABAS->value, $data['Product_Family__c']);
            }
        );
    }

    private function assertRequestLeadUpdate($leadId, $user, $contactType): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Lead,
            Action::Update,
            $leadId,
            function (Request $request) use ($user, $contactType) {
                $data = $request->data();
                $this->assertEquals($user->getContactEmail($contactType), $data['Email']);
                $this->assertEquals($user->getContactFirstName($contactType), $data['FirstName']);
                $this->assertEquals($user->getContactLastName($contactType), $data['LastName']);
                $this->assertEquals(SalesforceLeadSource::ERPPlanner->value, $data['LeadSource']);
            }
        );
    }

    private function assertRequestLeadGet($leadId): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Lead,
            Action::Get,
            $leadId,
            function (?Request $request) {
                $this->assertEquals('GET', $request->toPsrRequest()->getMethod());
            }
        );
    }

    private function assertRequestContactUpdate($contactId, $user, $contactType): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Contact,
            Action::Update,
            $contactId,
            function (Request $request) use ($user, $contactType) {
                $data = $request->data();
                $this->assertEquals($user->getContactEmail($contactType), $data['Email']);
                $this->assertEquals($user->getContactFirstName($contactType), $data['FirstName']);
                $this->assertEquals($user->getContactLastName($contactType), $data['LastName']);
                $this->assertEquals(SalesforceLeadSource::ERPPlanner->value, $data['LeadSource']);
            }
        );
    }

    private function assertRequestContactGet($contactId): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Contact,
            Action::Get,
            $contactId,
            function (?Request $request) {
                $this->assertEquals('GET', $request->toPsrRequest()->getMethod());
            }
        );
    }

    private function assertRequestTaskCreationForEventType($leadId, $ownerId, EventType $eventType): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Task,
            Action::Create,
            null,
            function (Request $request) use ($leadId, $ownerId, $eventType) {
                $data = $request->data();
                if ($eventType === EventType::UserRegistration) {
                    $subject = SalesforceTaskSubject::ChaseFormCompletion->value;
                    $priority = SalesforceTaskPriority::Normal->value;
                    $dueDate = Carbon::now()->addDay(7)->toDateString();
                } else {
                    $subject = SalesforceTaskSubject::FormReview->value;
                    $priority = SalesforceTaskPriority::High->value;
                    $dueDate = Carbon::now()->addDay()->toDateString();
                }
                $this->assertEquals($subject, $data['Subject']);
                $this->assertEquals($priority, $data['Priority']);
                $this->assertEquals($dueDate, $data['ActivityDate']);
                $this->assertEquals(SalesforceTaskStatus::Open->value, $data['Status']);
                $this->assertEquals($leadId, $data['WhoId']);
                $this->assertEquals($ownerId, $data['OwnerId']);
            }
        );
    }

    private function assertRequestTaskCreationForUserRegistered($leadId, $ownerId): self
    {
        return $this->assertRequestTaskCreationForEventType($leadId, $ownerId, EventType::UserRegistration);
    }

    private function assertRequestTaskCreationForDocumentExport($leadId, $ownerId): self
    {
        return $this->assertRequestTaskCreationForEventType($leadId, $ownerId, EventType::DocumentExport);
    }

    private function assertRequestTaskUpdateForDocumentExport($taskId, $leadId, $ownerId): self
    {
        return $this->assertRequest(
            SalesforceObjectType::Task,
            Action::Update,
            $taskId,
            function (Request $request) use ($leadId, $ownerId) {
                $data = $request->data();
                $this->assertEquals(SalesforceTaskSubject::FormReview->value, $data['Subject']);
                $this->assertEquals(SalesforceTaskPriority::High->value, $data['Priority']);
                $this->assertEquals(Carbon::now()->addDay()->toDateString(), $data['ActivityDate']);
                $this->assertEquals(SalesforceTaskStatus::Open->value, $data['Status']);
                $this->assertEquals($leadId, $data['WhoId']);
                $this->assertNotContains('OwnerId', $data);
            }
        );
    }

    private function assertRequestContentVersionCreation(): self
    {
        return $this->assertRequest(
            SalesforceObjectType::ContentVersion,
            Action::Create,
            null,
            function (Request $request) {
                $data = $request->data();
                $this->assertEquals('ERP-Form', $data['Title']);
                $this->assertEquals('ERP-Form.xlsx', $data['PathOnClient']);
                $this->assertEquals('S', $data['ContentLocation']);
            }
        );
    }

    private function assertRequestContentDocumentLinkCreation($taskId, $contentDocumentId): self
    {
        return $this->assertRequest(
            SalesforceObjectType::ContentDocumentLink,
            Action::Create,
            null,
            function (Request $request) use ($taskId, $contentDocumentId) {
                $data = $request->data();
                $this->assertEquals($contentDocumentId, $data['ContentDocumentId']);
                $this->assertEquals($taskId, $data['LinkedEntityId']);
                $this->assertEquals(SalesforceContentDocumentLinkVisibility::AllUsers->value, $data['Visibility']);
            }
        );
    }

    private function givenIsAUserWithContactData(ContactType $contactType, array $attributes = []): User
    {
        $attributes = array_merge(['country' => $this->faker->randomElement(['de', 'us', 'it', 'fr', 'br'])], $attributes);

        return match ($contactType) {
            ContactType::User    => User::factory()->registered()->create($attributes),
            ContactType::Company => User::factory()->create($attributes),
        };
    }

    private function givenIsASpecificationDocument(User $user): SpecificationDocument
    {
        $outputDir = storage_path('app/export');
        $document = new SpecificationDocument($outputDir, $user, []);
        $document->save(true);

        return $document;
    }
}
