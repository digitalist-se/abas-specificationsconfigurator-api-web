<?php

namespace App\Console\Commands;

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

class TestSalesforce extends Command
{
    use WithFaker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:salesforce {--createLead} {--getLead} {--leadByMail} {--updateLead} {--triggerRegisteredEvent} {--404}';

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
        $this->shouldNotFind = (bool) $this->option('404');
        match (true) {
            $this->option('createLead')             => $this->createLead(),
            $this->option('getLead')                => $this->getLead(),
            $this->option('triggerRegisteredEvent') => $this->triggerRegisteredEvent(),
            $this->option('leadByMail')             => $this->leadByMail(),
            $this->option('updateLead')             => $this->updateLead(),
            default                                 => $this->warn("No option selected, use {$this->signature}"),
        };

        return 0;
    }

    private function triggerRegisteredEvent(bool $dumpIt = true): void
    {
        $user = $this->createRegisteredUser();

        $this->log('triggering registered event', $user->toArray(), $dumpIt);

        event(new Registered($user));
    }

    private function createLead(bool $dumpIt = true): void
    {
        $user = $this->createRegisteredUser();

        $this->log('create lead', ['user_id' => $user->id], $dumpIt);

        $customProperties = [
            'Product_Family__c' => 'ABAS',
            'Status'            => 'Pre Lead',
            'LeadSource'        => 'ERP Planner - local API Test',
        ];

        $id = $this->crmService->createLead($user, $customProperties);

        $this->log('created lead', ['lead_id' => $id], $dumpIt);
    }

    private function getLead(bool $dumpIt = true): void
    {
        $salesforce = $this->salesforceLead();
        $id = $salesforce->lead_id;
        $user = $salesforce->user;

        if ($this->shouldNotFind) {
            $id = $this->faker()->uuid;
        }

        $this->log('get lead', $user->toArray(), $dumpIt);

        $lead = $this->crmService->getLead($id);

        $this->log('got lead', $lead, $dumpIt);
    }

    private function leadByMail(bool $dumpIt = true): void
    {
        $email = $this->salesforceLead()->user->email;

        if ($this->shouldNotFind) {
            $email = $this->faker()->email;
        }

        $this->log('search lead by email', ['email' => $email], $dumpIt);

        $id = $this->crmService->searchLeadByEmail($email);

        $this->log('search result', ['lead_id' => $id], $dumpIt);
    }

    private function updateLead(bool $dumpIt = true): void
    {
        $lead = $this->salesforceLead();
        $leadId = $lead->lead_id;
        $user = $lead->user;

        $this->updateUserWithProfile($user);

        $data = [
            'LeadSource' => 'ERP Planner - local API Test',
        ];

        $this->log('update lead', ['lead_id' => $leadId, 'user_id' => $user->id], $dumpIt);

        $result = $this->crmService->updateLead($leadId, $user, $data);

        $this->log('update result', ['success' => $result], $dumpIt);
    }

    private function createUserWithProfile(): User
    {
        return User::factory()
            ->create([
                'country' => $this->faker->randomElement(['de', 'us', 'it', 'fr', 'br']),
            ]);
    }

    private function updateUserWithProfile(User $user): User
    {
        $user->update([
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
        ]);

        return $user;
    }

    private function createRegisteredUser(): User
    {
        return User::factory()
            ->registered()
            ->create();
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
