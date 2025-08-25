<?php

namespace App\Console\Commands\Salesforce;

use App\Console\Commands\Salesforce\Action;
use App\CRM\Enums\SalesforceObjectType;
use App\CRM\Enums\SalesforceTaskPriority;
use App\CRM\Enums\SalesforceTaskStatus;
use App\CRM\Enums\SalesforceTaskSubject;
use App\CRM\Service\Auth\SalesforceAuthService;
use App\CRM\Service\Auth\SalesforceAuthTokenProvider;
use App\CRM\Service\SalesforceCRMService;
use App\Http\Resources\SpecificationDocument;
use App\Models\Salesforce;
use App\Models\User;
use Arr;
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
    protected $signature = 'test:salesforce {--O|object=} {--A|action=} {--U|user-id=} {--404}';

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

        // Find user if user-id is given, otherwise use latest or create
        $user = $userId ? User::find($userId) : $this->userFor($objectType, $action);
        if (! ($user instanceof User)) {
            $this->warn('User not found for action requiring user.');

            return 1;
        }

        match ($action) {
            Action::Create => $this->createObject($objectType, $user),
            Action::Get    => $this->getObject($objectType, $user),
            Action::Search => $this->searchObject($objectType, $user),
            Action::Update => $this->updateObject($objectType, $user),
        };

        return 0;
    }

    // Helper to get a default user for the object type
    private function userFor(SalesforceObjectType $objectType, Action $action): ?User
    {
        if ($action === Action::Create) {
            return match ($objectType) {
                SalesforceObjectType::Lead => $this->createRegisteredUser(),
                SalesforceObjectType::Task => $this->userWithSalesforce($this->faker->randomElement([SalesforceObjectType::Lead, SalesforceObjectType::Contact])),
                default                    => $this->createUserWithProfile(),
            };
        }

        $user = $this->userWithSalesforce($objectType);

        if ($objectType === SalesforceObjectType::Lead) {
            return $user;
        }

        return $this->updateUserWithProfile($user);
    }

    private function createObject(SalesforceObjectType $objectType, User $user, bool $dumpIt = true): void
    {
        $this->log("create {$objectType->value}", ['user_id' => $user->id], $dumpIt);

        $id = match ($objectType) {
            SalesforceObjectType::Lead => $this->crmService->createLead($user, [
                'Product_Family__c' => 'ABAS',
                'Status'            => 'Pre Lead',
                'LeadSource'        => 'ERP Planner - local API Test',
            ]),
            SalesforceObjectType::Contact => $this->crmService->createContact($user, [
                'LeadSource' => 'ERP Planner - local API Test',
                'AccountId'  => '001Pu00000U4XdeIAF',
            ]),
            SalesforceObjectType::Account => $this->crmService->createAccount($user, []),
            SalesforceObjectType::Task    => (function () use ($user) {
                if ($whoId = $user->salesforce->contact_id) {
                    $details = $this->crmService->getContact($whoId);
                } elseif ($whoId = $user->salesforce->lead_id) {
                    $details = $this->crmService->getLead($whoId);
                } else {
                    throw new RuntimeException('User has no contact_id or lead_id in salesforce relation');
                }

                $ownerId = Arr::get($details, 'OwnerId');

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
            SalesforceObjectType::ContentDocumentLink => throw new RuntimeException('Won\'t be implemented'),
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
            SalesforceObjectType::ContentDocument     => throw new RuntimeException('Won\'t be implemented'),
            SalesforceObjectType::ContentDocumentLink => throw new RuntimeException('Won\'t be implemented'),
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
            SalesforceObjectType::ContentVersion => 'content_document_id',
            default                              => Str::snake($objectType->value).'_id'
        };

        $this->log('search result', [$searchResultAttribute => $id], $dumpIt);
    }

    private function updateObject(SalesforceObjectType $objectType, User $user, bool $dumpIt = true): void
    {
        $id = $user->salesforce->objectId($objectType);

        $this->log("update {$objectType->value}", ['id' => $id, 'user_id' => $user->id], $dumpIt);

        $leadSource = [
            'LeadSource' => 'ERP Planner - local API Test',
        ];

        $result = match ($objectType) {
            SalesforceObjectType::Lead    => $this->crmService->updateLead($id, $user, $leadSource),
            SalesforceObjectType::Contact => $this->crmService->updateContact($id, $user, $leadSource),
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
            'contact_function'       => 'Geschäftsführer',
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
        Log::debug($message, $context);

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

    public function userWithSalesforce(SalesforceObjectType $objectType): User
    {
        $column = Salesforce::objectIdAttributeName($objectType);

        return Salesforce::whereNotNull($column)->latest()->firstOrFail()->user;
    }
}
