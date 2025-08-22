<?php

namespace App\Console\Commands\Salesforce;

use App\Console\Commands\Salesforce\Action;
use App\CRM\Enums\SalesforceObjectType;
use App\CRM\Service\Auth\SalesforceAuthService;
use App\CRM\Service\Auth\SalesforceAuthTokenProvider;
use App\CRM\Service\SalesforceCRMService;
use App\Models\Salesforce;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Log;
use Throwable;
use ValueError;

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
        $user = $userId ? User::find($userId) : $this->gettUserFor($objectType, $action);
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
    private function gettUserFor(SalesforceObjectType $objectType, Action $action): ?User
    {
        if ($action === Action::Create) {
            return match ($objectType) {
                SalesforceObjectType::Lead => $this->createRegisteredUser(),
                default                    => $this->createUserWithProfile(),
            };
        }

        $column = Salesforce::objectIdColumn($objectType);
        $user = Salesforce::whereNotNull($column)->latest()->firstOrFail()->user;

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
        };

        $this->log("created {$objectType->value}", [strtolower($objectType->value).'_id' => $id], $dumpIt);
    }

    private function getObject(SalesforceObjectType $objectType, User $user, bool $dumpIt = true): void
    {
        $id = $this->shouldNotFind ? $this->faker()->uuid : $user->salesforce->objectId($objectType);

        $this->log("get {$objectType->value}", ['id' => $id, 'user_id' => $user->id], $dumpIt);

        $result = match ($objectType) {
            SalesforceObjectType::Lead    => $this->crmService->getLead($id),
            SalesforceObjectType::Contact => $this->crmService->getContact($id),
            SalesforceObjectType::Account => $this->crmService->getAccount($id),
        };

        $this->log("got {$objectType->value}", $result, $dumpIt);
    }

    private function searchObject(SalesforceObjectType $objectType, User $user = null, bool $dumpIt = true): void
    {
        $searchValue = match ($objectType) {
            SalesforceObjectType::Account => $this->shouldNotFind ? $this->faker()->company() : $user->company,
            default                       => $this->shouldNotFind ? $this->faker()->email : ($user->contact_email ?: $user->email),
        };

        $this->log("search {$objectType->value}", ['search' => $searchValue], $dumpIt);

        $id = match ($objectType) {
            SalesforceObjectType::Lead    => $this->crmService->searchLeadByEmail($searchValue),
            SalesforceObjectType::Contact => $this->crmService->searchContactByEmail($searchValue),
            SalesforceObjectType::Account => $this->crmService->searchAccountByName($searchValue),
        };

        $this->log('search result', [strtolower($objectType->value).'_id' => $id], $dumpIt);
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
        };

        $this->log('update result', ['success' => $result], $dumpIt);
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
}
