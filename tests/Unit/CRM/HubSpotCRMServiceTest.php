<?php

namespace Tests\Unit\CRM;

use App\CRM\Enums\HubSpotEventType;
use App\CRM\Service\HubSpotCRMService;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Http\Resources\SpecificationDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HubSpotCRMServiceTest extends TestCase
{
    use WithFaker;

    protected function expectedEventNames(): array
    {
        return [
            HubSpotEventType::DocumentExport->value => 'hs-event-name-for-document-export',
            HubSpotEventType::UserRegistered->value => 'hs-event-name-for-user-registered',
        ];
    }

    protected function expectedEventName(HubSpotEventType $eventType): string
    {
        return Arr::get($this->expectedEventNames(), $eventType->value);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.hubSpot.enabled', true);
        Config::set('services.hubSpot.events', $this->expectedEventNames());
    }

    /**
     * @test
     */
    public function it_authorizes_requests()
    {
        Http::fake([
            '*' => Http::sequence([
                Http::response(['results' => [['id' => 'fakeId'], ['id' => 'notUsedId']]]),
                Http::response(['id' => 'fakeId']),
            ]),
        ]);

        $user = $this->givenIsAUserWithCrmIds();

        $service = $this->app->make(HubSpotCRMService::class);
        $service->updateCompany($user);

        Http::assertSent(function (?Request $request, ?Response $response) {
            $this->assertNotNull($request);
            $authHeader = $request->header('Authorization')[0] ?? null;
            $token = Config::get('services.hubSpot.accessToken');
            $this->assertEquals("Bearer {$token}", $authHeader);

            return true;
        });
    }

    /**
     * @test
     */
    public function it_can_not_track_document_export_without_crm_user()
    {
        Http::fake();
        $user = User::factory()->make();
        $document = $this->givenIsASpecificationDocument($user);
        $this->app->make(HubSpotCRMService::class);
        $service = $this->app->make(HubSpotCRMService::class);
        $service->trackDocumentExport(new ExportedDocument($user, $document));
        Http::assertNothingSent();
    }

    public function provideCrmContactIds()
    {
        return [
            'just_user' => ['xyz', null],
            'both'      => ['xyz', 'üöä'],
        ];
    }

    /**
     * @dataProvider provideCrmContactIds
     * @test
     */
    public function it_can_track_document_export($crmUserContactId, $crmCompanyContactId)
    {
        $crmCompanyId = (string) $this->faker->numberBetween(1000);
        $crmDealIds = collect(array_fill(0, $this->faker->numberBetween(0, 5), ''))
            ->map(fn () => (string) $this->faker->numberBetween(1000));

        Http::fake([
            '*' => Http::sequence([
                Http::response(['id' => 'fakeId']), // Create Event
                Http::response(['id' => 'fakeId']), // Upload File
                Http::response(['results' => [['id' => $crmCompanyId]]]), // Get CompanyId
                Http::response(['results' => $crmDealIds->map(fn ($id) => ['id' => $id])->toArray()]), // Get DealIds associated with  Company
                Http::response(['id' => 'fakeId']), // Create Engagement Note
            ]),
        ]);

        $expectedFolderId = $this->faker->numberBetween(1000, 10000);
        Config::set('services.hubSpot.folder.id', $expectedFolderId);
        $user = $this->givenIsAUserWithCrmIds($crmUserContactId, $crmCompanyContactId);
        $document = $this->givenIsASpecificationDocument($user);

        $service = $this->app->make(HubSpotCRMService::class);
        $service->trackDocumentExport(new ExportedDocument($user, $document));

        // Then we expect create event request
        Http::assertSent(function (?Request $request, ?Response $response) use ($crmUserContactId) {
            if ($request === null) {
                return false;
            }

            if ('/events/v3/send' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertEquals(
                [
                    'eventName'  => $this->expectedEventName(HubSpotEventType::DocumentExport),
                    'objectType' => 'contacts',
                    'objectId'   => $crmUserContactId,
                ],
                $request->data());

            return true;
        });

        // And we expect the file upload request
        Http::assertSent(function (?Request $request, ?Response $response) use ($expectedFolderId) {
            if ($request === null) {
                return false;
            }
            if ('/files/v3/files' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertTrue($request->hasFile('file'));

            $data = collect($request->data())->flatMap(function ($part) {
                switch ($part['name']) {
                    case 'options':
                        $part['contents'] = json_decode($part['contents'], true);
                    case 'folderId':
                        return [
                            $part['name'] => $part['contents'],
                        ];
                }

                return [];
            });

            $this->assertEquals(
                [
                    'folderId' => $expectedFolderId,
                    'options'  => [
                        'access'                      => 'PRIVATE',
                        'overwrite'                   => false,
                        'duplicateValidationStrategy' => 'NONE',
                        'duplicateValidationScope'    => 'EXACT_FOLDER',
                    ],
                ],
                $data->toArray());

            return true;
        });

        // And we expect the get companyId request
        Http::assertSent(function (?Request $request, ?Response $response) use ($crmUserContactId) {
            if ($request === null) {
                return false;
            }

            $path = $request->toPsrRequest()->getUri()->getPath();
            $expectedPath = "/crm/v3/objects/contacts/$crmUserContactId/associations/company";
            if ($expectedPath !== $path) {
                return false;
            }

            return true;
        });

        // And we expect the get dealIds of company request
        Http::assertSent(function (?Request $request, ?Response $response) use ($crmCompanyId) {
            if ($request === null) {
                return false;
            }

            $path = $request->toPsrRequest()->getUri()->getPath();
            $expectedPath = "/crm/v3/objects/companies/$crmCompanyId/associations/deal";
            if ($expectedPath !== $path) {
                return false;
            }

            return true;
        });

        // And we expect the create engagement request
        Http::assertSent(function (?Request $request, ?Response $response) use ($crmCompanyContactId, $crmUserContactId, $crmCompanyId, $crmDealIds) {
            if ($request === null) {
                return false;
            }

            if ('/engagements/v1/engagements' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertEquals(
                [
                    'active' => true,
                    'type'   => 'NOTE',
                ],
                $request->data()['engagement']);

            $this->assertEquals(
                [
                    'contactIds' => collect([$crmUserContactId, $crmCompanyContactId])->filter()->toArray(),
                    'companyIds' => [$crmCompanyId],
                    'dealIds'    => $crmDealIds->toArray(),
                ],
                $request->data()['associations']);

            $this->assertEquals(
                [
                    [
                        'id' => 'fakeId',
                    ],
                ],
                $request->data()['attachments']);
            $this->assertStringStartsWith('Neue Lastenheftgenerierung:<br/><br/>', $request->data()['metadata']['body']);

            return true;
        });

        unlink($document->outputZipFilename());
    }

    /**
     * @test
     */
    public function it_can_track_user_registered()
    {
        Http::fake([
            '*' => Http::sequence([
                Http::response(['id' => 'fakeId']),
            ]),
        ]);

        $user = $this->givenIsAUserWithCrmIds('xyz');

        $service = $this->app->make(HubSpotCRMService::class);
        $service->trackUserRegistered(new Registered($user));

        Http::assertSent(function (?Request $request, ?Response $response) {
            if ($request === null) {
                return false;
            }

            if ('/events/v3/send' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertEquals(
                [
                    'eventName'  => $this->expectedEventName(HubSpotEventType::UserRegistered),
                    'objectType' => 'contacts',
                    'objectId'   => 'xyz',
                ],
                $request->data());

            return true;
        });
    }

    /**
     * @test
     */
    public function it_can_update_company()
    {
        Http::fake([
            '*' => Http::sequence([
                Http::response(['results' => [['id' => 'fakeId'], ['id' => 'notUsedId']]]),
                Http::response(['id' => 'fakeId']),
            ]),
        ]);

        $user = $this->givenIsAUserWithCrmIds('xyz');

        $service = $this->app->make(HubSpotCRMService::class);
        $service->updateCompany($user);

        Http::assertSent(function (?Request $request, ?Response $response) {
            if ($request === null) {
                return false;
            }

            if ('/crm/v3/objects/contacts/xyz/associations/company' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            return true;
        });

        Http::assertSent(function (?Request $request, ?Response $response) use ($user) {
            if ($request === null) {
                return false;
            }

            if ('/crm/v3/objects/companies/fakeId' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertEquals($user->company, Arr::get($request->data(), 'properties.name'));

            return true;
        });
    }

    public function upsertContactUseCases(): array
    {
        return [
            'no_conctact_id_and_non_existing_email_expects_no_update' => [false, false, false],
            'no_conctact_id_and_existing_email_expects_update'        => [false, true, true],
            'conctact_id_and_existing_email_expects_update'           => [true, true, true],
        ];
    }

    /**
     * @dataProvider upsertContactUseCases
     * @test
     */
    public function it_upserts_user_contact_with_respect_of_existing_email_addresses_at_hubspot(bool $hasContactId, bool $emailExistsOnHubSpot, bool $shouldUpdateContact)
    {
        $type = ContactType::User;
        $user = $this->givenIsAUserWithCrmIds($hasContactId ? 'xyz' : null);
        $userContactId = $user->getCrmContactId($type) ?? 'hubSpotId';

        $httpSequence = collect([
            'search_with_email' => Http::response(['results' => ($emailExistsOnHubSpot ? [['id' => $userContactId]] : [])]),
            'update_or_create'  => Http::response(['id' => $userContactId]),
        ]);

        if ($hasContactId) {
            $httpSequence->pull('search_with_email');
        }

        Http::fake([
            '*' => Http::sequence($httpSequence->toArray()),
        ]);

        $service = $this->app->make(HubSpotCRMService::class);
        $service->upsertContact($user, $type);

        if (! $hasContactId) {
            Http::assertSent(function (?Request $request, ?Response $response) use ($user) {
                if ($request === null) {
                    return false;
                }

                $path = $request->toPsrRequest()->getUri()->getPath();
                $expectedPath = '/crm/v3/objects/contacts/search';
                if ($expectedPath !== $path) {
                    return false;
                }

                $searchedEmail = Arr::get($request->data(), 'filterGroups.0.filters.0.value');
                if ($searchedEmail !== $user->email) {
                    return false;
                }

                return true;
            });
        }

        Http::assertSent(function (?Request $request, ?Response $response) use ($shouldUpdateContact, $userContactId) {
            if ($request === null) {
                return false;
            }

            $path = $request->toPsrRequest()->getUri()->getPath();
            $expectedPath = '/crm/v3/objects/contacts'.($shouldUpdateContact ? "/$userContactId" : '');
            if ($expectedPath !== $path) {
                return false;
            }
            $expectedMethod = $shouldUpdateContact ? 'PATCH' : 'POST';
            $method = $request->toPsrRequest()->getMethod();
            if ($expectedMethod !== $method) {
                return false;
            }

            return true;
        });

        $this->assertEquals($userContactId, $user->getCrmContactId($type));
    }

    public function provideCustomContactProperties()
    {
        return [
            'no_custom_properties' => [[]],
            'custom_properties'    => [['custum_prop' => 'custom_value']],
        ];
    }

    /**
     * @dataProvider provideCustomContactProperties
     * @test
     */
    public function it_upserts_user_contact_with_custom_contact_properties(array $customProperties)
    {
        // Given there is a user without crm contact id
        $type = ContactType::User;
        $user = $this->givenIsAUserWithCrmIds(null);

        // And given there is a http fake for create and update contact requests
        $userContactId = 'hubSpotId';
        Http::fake([
            '*' => Http::sequence([
                'search_with_email' => Http::response(['results' => []]),
                'create'            => Http::response(['id' => $userContactId]),
                'update'            => Http::response(['id' => $userContactId]),
            ]),
        ]);

        // And given there is a HubSpotCRMService istance
        $service = $this->app->make(HubSpotCRMService::class);

        // When we upsert contact
        $service->upsertContact($user, $type, $customProperties);

        // Then we expect a create request
        Http::assertSent(function (?Request $request, ?Response $response) use ($customProperties) {
            if ($request === null) {
                return false;
            }

            $path = $request->toPsrRequest()->getUri()->getPath();
            $expectedPath = '/crm/v3/objects/contacts';
            if ($expectedPath !== $path) {
                return false;
            }
            $expectedMethod = 'POST';
            $method = $request->toPsrRequest()->getMethod();
            if ($expectedMethod !== $method) {
                return false;
            }

            if (! empty($customProperties)) {
                $properties = collect($request->data()['properties']);
                if (! collect($customProperties)->every(fn ($value, $key) => $properties->get($key) === $value)) {
                    return false;
                }
            }

            return true;
        });

        // And we expect the user object was updated with contact crm id
        $this->assertEquals($userContactId, $user->getCrmContactId($type));

        // When we upsert contact again
        $service->upsertContact($user, $type, $customProperties);

        // Then we expect an update request
        Http::assertSent(function (?Request $request, ?Response $response) use ($userContactId, $customProperties) {
            if ($request === null) {
                return false;
            }

            $path = $request->toPsrRequest()->getUri()->getPath();
            $expectedPath = "/crm/v3/objects/contacts/$userContactId";
            if ($expectedPath !== $path) {
                return false;
            }
            $expectedMethod = 'PATCH';
            $method = $request->toPsrRequest()->getMethod();
            if ($expectedMethod !== $method) {
                return false;
            }

            if (! empty($customProperties)) {
                $properties = collect($request->data()['properties']);
                if (! collect($customProperties)->every(fn ($value, $key) => $properties->get($key) === $value)) {
                    return false;
                }
            }

            return true;
        });
    }

    private function givenIsAUserWithCrmIds(?string $userContactId = 'xyz', ?string $companyContactId = null): User
    {
        return User::factory()->make([
            'crm_user_contact_id'    => $userContactId,
            'crm_company_contact_id' => $companyContactId,
        ]);
    }

    /**
     * @param mixed $user
     *
     * @return SpecificationDocument
     * @throws \App\Exceptions\GenerateExcelException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function givenIsASpecificationDocument(mixed $user): SpecificationDocument
    {
        $outputDir = storage_path('app/export');
        $document = new SpecificationDocument($outputDir, $user, []);
        $document->save();

        return $document;
    }
}
