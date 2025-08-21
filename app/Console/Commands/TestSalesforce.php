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
use Log;

class TestSalesforce extends Command
{
    use WithFaker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:salesforce {--createLead} {--getLead} {--leadByMail} {--triggerRegisteredEvent} {--404}';

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
            default                                 => $this->warn("No option selected, use {$this->signature}"),
        };

        return 0;
    }

    private function triggerRegisteredEvent(bool $dumpIt = true): void
    {
        $user = $this->createUser();

        $this->log('triggering registered event', $user->toArray(), $dumpIt);

        event(new Registered($user));
    }

    private function createLead(bool $dumpIt = true): void
    {
        $user = $this->createUser();

        $this->log('create lead', ['id' => $user->id], $dumpIt);

        $customProperties = [
            'Product_Family__c' => 'ABAS',
            'Status'            => 'Pre Lead',
            'LeadSource'        => 'ERP Planner - local API Test',
        ];

        $response = $this->crmService->createLead($user, $customProperties);

        $this->log('created lead', $response->json(), $dumpIt);
    }

    private function getLead(bool $dumpIt = true): void
    {
        $id = $this->salesforceLead()->lead_id;

        if ($this->shouldNotFind) {
            $id = $this->faker()->uuid;
        }

        $this->log('get lead', ['id' => $id], $dumpIt);

        $response = $this->crmService->getLead($id);

        $this->log('got lead', $response->json(), $dumpIt);
    }

    private function leadByMail(bool $dumpIt = true): void
    {
        $email = $this->salesforceLead()->user->email;

        if ($this->shouldNotFind) {
            $email = $this->faker()->email;
        }

        $this->log('search lead by email', ['email' => $email], $dumpIt);

        $response = $this->crmService->searchLeadyByEmail($email);

        $this->log('search result', $response->json(), $dumpIt);
    }

    private function createUser(): User
    {
        return User::factory()->create([
            'country' => $this->faker->randomElement(['de', 'us', 'it', 'fr', 'br']),
        ]);
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
        return Salesforce::whereNotNull('lead_id')->firstOrFail();
    }
}
