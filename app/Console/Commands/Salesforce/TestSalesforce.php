<?php

namespace App\Console\Commands\Salesforce;

use App\CRM\Enums\SalesforceContentDocumentLinkVisibility;
use App\CRM\Enums\SalesforceLeadProductFamily;
use App\CRM\Enums\SalesforceLeadSource;
use App\CRM\Enums\SalesforceLeadStatus;
use App\CRM\Enums\SalesforceObjectType;
use App\CRM\Enums\SalesforceTaskPriority;
use App\CRM\Enums\SalesforceTaskStatus;
use App\CRM\Enums\SalesforceTaskSubject;
use App\CRM\Service\Auth\SalesforceAuthService;
use App\CRM\Service\Auth\SalesforceAuthTokenProvider;
use App\CRM\Service\SalesforceCRMService;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Http\Resources\SpecificationDocument;
use App\Models\Salesforce;
use App\Models\User;
use Arr;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Log;
use RuntimeException;
use Str;
use Throwable;

class TestSalesforce extends Command
{
    use WithFaker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:salesforce {--E|event=} {--O|object=} {--A|action=} {--U|user-id=} {--C|contact=User} {--404} {--show-user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test several salesforce operations';

    private bool $shouldNotFind;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private SalesforceAuthTokenProvider $authTokenProvider,
        private SalesforceAuthService $authService,
        private SalesforceCRMService $crmService,
    ) {
        parent::__construct();

        $this->setUpFaker();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! app()->environment('local')) {
            $this->warn('This command must be executed at local environment only.');

            return 1;
        }

        return match ($this->hasOption('event')) {
            true  => $this->handleEvent(),
            false => $this->handleAction(),
        };
    }

    public function handleAction(): int
    {
        $userId = $this->option('user-id');
        $this->shouldNotFind = (bool) $this->option('404');

        try {
            $objectType = SalesforceObjectType::from($this->option('object'));
        } catch (Throwable) {
            $this->warn('Missing or invalid --object option. Use one of: Lead, Contact, Account');

            return 1;
        }

        try {
            $action = Action::from($this->option('action'));
        } catch (Throwable) {
            $this->warn('Missing or invalid --action option. Use one of: get, search, create, update');

            return 1;
        }

        try {
            $contactType = ContactType::from($this->option('contact'));
        } catch (Throwable) {
            $this->warn('Missing or invalid --contact option. Use one of: User, Company');

            return 1;
        }

        // Find user if user-id is given, otherwise use latest or create
        $user = $userId ? User::find($userId) : $this->userFor($objectType, $action, $contactType);
        if (! ($user instanceof User)) {
            $this->warn('User not found for action requiring user.');

            return 1;
        }

        $this->showUser($user);

        match ($action) {
            Action::Create => $this->createObject($objectType, $user, $contactType),
            Action::Get    => $this->getObject($objectType, $user),
            Action::Search => $this->searchObject($objectType, $user),
            Action::Update => $this->updateObject($objectType, $user, $contactType),
        };

        return 0;
    }

    public function handleEvent(): int
    {
        try {
            $eventType = EventType::from($this->option('event'));
        } catch (Throwable) {
            $this->warn('Missing or invalid --event option. Use one of: registered, exported');

            return 1;
        }

        $userId = $this->option('user-id') ?? throw new RuntimeException('Missing --user-id option');
        $user = User::find($userId);
        if (! ($user instanceof User)) {
            $this->warn('User not found for event.');

            return 1;
        }

        $this->showUser($user);

        $this->log("handle event {$eventType->value}", ['user_id' => $user->id]);
        match ($eventType) {
            EventType::UserRegistered => $this->crmService->handleUserRegistered(new Registered($user)),
            EventType::DocumentExport => $this->crmService->handleDocumentExport(
                new ExportedDocument($user, $this->generateSpecificationDocument($user))
            ),
        };

        return 0;
    }

    private function userFor(SalesforceObjectType $objectType, Action $action, ContactType $contactType): ?User
    {
        if ($action === Action::Create) {
            return match ($objectType) {
                SalesforceObjectType::Task                => $this->userWithSalesforce($this->faker->randomElements([SalesforceObjectType::Lead, SalesforceObjectType::Contact], 1)),
                SalesforceObjectType::ContentDocumentLink => $this->userWithSalesforce([SalesforceObjectType::Task, SalesforceObjectType::ContentDocument]),
                default                                   => match ($contactType) {
                    ContactType::User    => $this->createRegisteredUser(),
                    ContactType::Company => $this->createUserWithProfile(),
                },
            };
        }

        $user = $this->userWithSalesforce([$objectType]);

        return match ($contactType) {
            ContactType::User    => $user,
            ContactType::Company => $this->updateUserWithProfile($user),
        };
    }

    private function createObject(SalesforceObjectType $objectType, User $user, ContactType $contactType, bool $dumpIt = true): void
    {
        $this->log("create {$objectType->value}", ['user_id' => $user->id], $dumpIt);

        $id = match ($objectType) {
            SalesforceObjectType::Lead => $this->crmService->createLead($user, [
                'Product_Family__c' => SalesforceLeadProductFamily::ABAS->value,
                'Status'            => SalesforceLeadStatus::PreLead->value,
                'LeadSource'        => SalesforceLeadSource::ERPPlanner->value.' - local API Test',
            ], $contactType),
            SalesforceObjectType::Contact => $this->crmService->createContact($user, [
                'LeadSource' => SalesforceLeadSource::ERPPlanner->value.' - local API Test',
                'AccountId'  => '001Pu00000U4XdeIAF',
            ], $contactType),
            SalesforceObjectType::Account => $this->crmService->createAccount($user, []),
            SalesforceObjectType::Task    => (function () use ($user, $dumpIt) {
                if ($whoId = $user->salesforce->contact_id) {
                    $this->log('contact for task', ['whoId' => $whoId], $dumpIt);
                    $details = $this->crmService->getContact($whoId);
                } elseif ($whoId = $user->salesforce->lead_id) {
                    $this->log('lead for task', ['whoId' => $whoId], $dumpIt);
                    $details = $this->crmService->getLead($whoId);
                } else {
                    throw new RuntimeException('User has no contact_id or lead_id in salesforce relation');
                }

                $ownerId = Arr::get($details, 'OwnerId');
                $this->log('owner for task', ['owner_id' => $ownerId], $dumpIt);

                if (empty($ownerId)) {
                    throw new RuntimeException('Could not determine OwnerId from salesforce details');
                }

                return $this->crmService->createTask($user, [
                    'Subject'      => SalesforceTaskSubject::FormReview->value,
                    'ActivityDate' => Carbon::now()->addDay()->format('Y-m-d'),
                    'OwnerId'      => $ownerId,
                    'WhoId'        => $whoId,
                    'Status'       => SalesforceTaskStatus::Open->value,
                    'Priority'     => SalesforceTaskPriority::High->value,
                ]);
            }
            )(),
            SalesforceObjectType::ContentVersion => $this->crmService->createContentVersion(
                $user,
                $this->generateSpecificationDocument($user),
                [
                    'Title'           => 'ERP-Form',
                    'PathOnClient'    => 'ERP-Form.xlsx',
                    'ContentLocation' => 'S',
                ]),
            SalesforceObjectType::ContentDocument     => throw new RuntimeException('Won\'t be implemented'),
            SalesforceObjectType::ContentDocumentLink => (function () use ($user) {
                $contentDocumentId = $user->salesforce->content_document_id ?? throw new RuntimeException('User has no content_document_id in salesforce relation');
                $taskId = $user->salesforce->task_id ?? throw new RuntimeException('User has no task_id in salesforce relation');

                return $this->crmService->createContentDocumentLink($user, [
                    'ContentDocumentId' => $contentDocumentId,
                    'LinkedEntityId'    => $taskId,
                    'Visibility'        => SalesforceContentDocumentLinkVisibility::AllUsers->value,
                ]);
            }
            )(),
        };

        $this->log("created {$objectType->value}", [strtolower($objectType->value).'_id' => $id], $dumpIt);
    }

    private function getObject(SalesforceObjectType $objectType, User $user, bool $dumpIt = true): void
    {
        $id = $this->shouldNotFind ? $this->faker()->uuid : $user->salesforce->objectId($objectType);

        $this->log("get {$objectType->value}", ['id' => $id, 'user_id' => $user->id], $dumpIt);

        $result = match ($objectType) {
            SalesforceObjectType::Lead                => $this->crmService->getLead($id),
            SalesforceObjectType::Contact             => $this->crmService->getContact($id),
            SalesforceObjectType::Account             => $this->crmService->getAccount($id),
            SalesforceObjectType::Task                => $this->crmService->getTask($id),
            SalesforceObjectType::ContentVersion      => $this->crmService->getContentVersion($id),
            SalesforceObjectType::ContentDocument     => $this->crmService->getContentDocument($id),
            SalesforceObjectType::ContentDocumentLink => $this->crmService->getContentDocumentLink($id),
        };

        $this->log("got {$objectType->value}", $result, $dumpIt);
    }

    private function searchObject(SalesforceObjectType $objectType, User $user = null, bool $dumpIt = true): void
    {
        $searchValues = match ($objectType) {
            SalesforceObjectType::Account => [$this->shouldNotFind ? $this->faker()->company() : $user->company],
            SalesforceObjectType::Task    => [
                SalesforceTaskSubject::FormReview,
                $this->shouldNotFind ? $this->faker()->uuid : ($user->salesforce->contact_id ?? $user->salesforce->lead_id),
                SalesforceTaskStatus::Open,
            ],
            SalesforceObjectType::ContentVersion => [$this->shouldNotFind ? $this->faker()->uuid : $user->salesforce->content_version_id],
            default                              => [$this->shouldNotFind ? $this->faker()->email : ($user->contact_email ?: $user->email)],
        };

        $this->log("search {$objectType->value}", ['search' => $searchValues], $dumpIt);

        $id = match ($objectType) {
            SalesforceObjectType::Lead                => $this->crmService->searchLeadBy(...$searchValues),
            SalesforceObjectType::Contact             => $this->crmService->searchContactBy(...$searchValues),
            SalesforceObjectType::Account             => $this->crmService->searchAccountBy(...$searchValues),
            SalesforceObjectType::Task                => $this->crmService->searchTaskBy(...$searchValues),
            SalesforceObjectType::ContentVersion      => $this->crmService->searchContentVersionForContentDocumentBy(...$searchValues),
            SalesforceObjectType::ContentDocument     => throw new RuntimeException('Won\'t be implemented'),
            SalesforceObjectType::ContentDocumentLink => throw new RuntimeException('Won\'t be implemented'),
        };

        $searchResultAttribute = match ($objectType) {
            SalesforceObjectType::ContentVersion => (function () use ($user, $id) {
                $user->salesforce->saveObjectId($id, SalesforceObjectType::ContentDocument);

                return 'content_document_id';
            }
            )(),
            default => Str::snake($objectType->value).'_id'
        };

        $this->log('search result', [$searchResultAttribute => $id], $dumpIt);
    }

    private function updateObject(SalesforceObjectType $objectType, User $user, ContactType $contactType, bool $dumpIt = true): void
    {
        $id = $user->salesforce->objectId($objectType);

        $this->log("update {$objectType->value}", ['id' => $id, 'user_id' => $user->id], $dumpIt);

        $leadSource = [
            'LeadSource' => 'ERP Planner - local API Test',
        ];

        $result = match ($objectType) {
            SalesforceObjectType::Lead    => $this->crmService->updateLead($id, $user, $leadSource, $contactType),
            SalesforceObjectType::Contact => $this->crmService->updateContact($id, $user, $leadSource, $contactType),
            SalesforceObjectType::Account => $this->crmService->updateAccount($id, $user, []),
            SalesforceObjectType::Task    => $this->crmService->updateTask($id, $user, [
                'Subject'      => SalesforceTaskSubject::FormReview->value,
                'ActivityDate' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'WhoId'        => $user->salesforce->contact_id ?? $user->salesforce->lead_id,
                'Status'       => SalesforceTaskStatus::Open->value,
                'Priority'     => SalesforceTaskPriority::High->value,
            ]),
            SalesforceObjectType::ContentVersion      => throw new RuntimeException('Won\'t be implemented'),
            SalesforceObjectType::ContentDocument     => throw new RuntimeException('Won\'t be implemented'),
            SalesforceObjectType::ContentDocumentLink => throw new RuntimeException('Won\'t be implemented'),
        };

        $this->log('update result', ['success' => $result], $dumpIt);
    }

    private function generateSpecificationDocument(User $user): SpecificationDocument
    {
        $this->log('generate specification document', ['user_id' => $user->id]);

        $specificationDocument = new SpecificationDocument(
            storage_path('app/export'),
            $user,
            $user->answers
        );
        $specificationDocument->save(true);

        $this->log('generated specification document', ['path' => $specificationDocument->outputExcelFilename()]);

        return $specificationDocument;
    }

    private function createUserWithProfile(): User
    {
        return User::factory()
            ->create([
                'country' => $this->faker->randomElement(['de', 'us', 'it', 'fr', 'br']),
            ]);
    }

    private function updateUserWithProfile(User $user, array $attributes = []): User
    {
        $defaults = [
            'country'                => $this->faker()->randomElement(['de', 'us', 'it', 'fr', 'br']),
            'sex'                    => $this->faker()->boolean() ? 'm' : 'w',
            'phone'                  => $this->faker()->phoneNumber(),
            'website'                => $this->faker()->url(),
            'street'                 => $this->faker()->streetAddress(),
            'additional_street_info' => $this->faker()->streetAddress(),
            'zipcode'                => $this->faker()->randomNumber(5),
            'city'                   => $this->faker()->city(),
            'company_name'           => $user->user_company ?? $this->faker()->company(),
            'contact_first_name'     => $this->faker()->firstName(),
            'contact_last_name'      => $this->faker()->lastName(),
            'contact_email'          => $this->faker()->safeEmail(),
            'contact_function'       => $this->faker()->jobTitle(),
            'email_verified_at'      => Carbon::now()->subDay(),
        ];

        $user->update(array_merge($defaults, $attributes));

        return $user;
    }

    private function createRegisteredUser(array $attributes = []): User
    {
        return User::factory()
            ->registered()
            ->create($attributes);
    }

    private function log(string $message, array $context, bool $dumpIt = true): void
    {
        Log::channel('salesforce')->debug(sprintf('[TEST] %s', $message), $context);

        if ($dumpIt) {
            dump($message, $context);
        }
    }

    public function salesforceLead(): Salesforce|Model|Builder
    {
        return Salesforce::whereNotNull('lead_id')
            ->latest()
            ->firstOrFail();
    }

    /**
     * @param SalesforceObjectType[] $objectTypes
     */
    public function userWithSalesforce(array $objectTypes): User
    {
        $columns = Arr::map($objectTypes, fn ($objectType) => Salesforce::objectIdAttributeName($objectType));

        return Salesforce::whereNotNull($columns)->latest()->firstOrFail()->user;
    }

    private function showUser(User $user): void
    {
        if ((bool) $this->option('show-user')) {
            $this->log('User', $user->toArray());
        }
    }
}
