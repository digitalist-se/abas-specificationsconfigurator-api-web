<?php

namespace App\CRM\Service;

use function abort;
use function app;
use App\CRM\Adapter\CompanyAdapter;
use App\CRM\Adapter\ContactAdapter;
use App\CRM\Adapter\TrackEventAdapter;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class HubSpotCRMService implements CRMService
{
    protected ?string $baseUrl = null;

    protected ?string $apiKey = null;

    /**
     * @var array<string, string>
     */
    protected array $events = [];

    public function __construct($options = [])
    {
        if (isset($options['apiKey'])) {
            $this->apiKey = $options['apiKey'];
        }
        if (isset($options['baseUrl'])) {
            $this->baseUrl = $options['baseUrl'];
        }
        if (isset($options['events'])) {
            $this->events = $options['events'];
        }
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    protected function getCompanyAdapter(): CompanyAdapter
    {
        return app()->make(CompanyAdapter::class);
    }

    protected function getContactAdapter(): ContactAdapter
    {
        return app()->make(ContactAdapter::class);
    }

    protected function getTrackEventAdapter(string $eventName): TrackEventAdapter
    {
        $eventName = $this->events[$eventName] ?? $eventName;

        return app()->make(TrackEventAdapter::class, ['eventName' => $eventName]);
    }

    public function createCompany(User $user): bool
    {
        $adapter = $this->getCompanyAdapter();
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::post($this->createUrl('/crm/v3/objects/companies'), $requestBody)
            ->throw()
            ->json();
        if (isset($response['id'])) {
            $companyId = $response['id'];
            $user->update([
                'crm_company_id' => $companyId,
            ]);
        }

        return true;
    }

    public function createContact(User $user): bool
    {
        $adapter = $this->getContactAdapter();
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::post($this->createUrl('/crm/v3/objects/contacts'), $requestBody)
            ->throw()
            ->json();
        if (isset($response['id'])) {
            $companyId = $response['id'];
            $user->update([
                'crm_contact_id' => $companyId,
            ]);
        }

        return true;
    }

    public function updateCompany(User $user): bool
    {
        if (empty($user->crm_company_id)) {
            abort(500, 'missing company id');
        }
        $adapter = $this->getCompanyAdapter();
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::put($this->createUrl('/crm/v3/objects/companies/'.$user->crm_company_id), $requestBody)
            ->throw()
            ->json();
        if (isset($response['id'])) {
            $companyId = $response['id'];
            $user->update([
                'crm_company_id' => $companyId,
            ]);
        }

        return true;
    }

    public function updateContact(User $user): bool
    {
        if (empty($user->crm_contact_id)) {
            abort(500, 'missing contact id');
        }
        $adapter = $this->getContactAdapter();
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::put($this->createUrl('/crm/v3/objects/contacts/'.$user->crm_contact_id), $requestBody)
            ->throw()
            ->json();
        if (isset($response['id'])) {
            $companyId = $response['id'];
            $user->update([
                'crm_contact_id' => $companyId,
            ]);
        }

        return true;
    }

    public function linkContactToCompany(User $user): bool
    {
        if (empty($user->crm_company_id)) {
            abort(500, 'missing company id');
        }
        if (empty($user->crm_contact_id)) {
            abort(500, 'missing contact id');
        }
        $response = Http::put($this->createUrl('/crm/v3/objects/contacts/'.$user->crm_contact_id.'/associations/companies/'.$user->crm_company_id.'/contact_to_company'), [
        ])
            ->throw()
            ->json();

        return isset($response['id']);
    }

    public function deleteCompany(User $user): bool
    {
        if (empty($user->crm_company_id)) {
            abort(500, 'missing company id');
        }
        $responseStatus = Http::delete($this->createUrl('/crm/v3/objects/companies/'.$user->crm_company_id))
                ->throw()
                ->status();
        $user->update(['crm_company_id' => null]);

        return 204 === $responseStatus;
    }

    public function deleteContact(User $user): bool
    {
        if (empty($user->crm_contact_id)) {
            abort(500, 'missing contact id');
        }
        $responseStatus = Http::delete($this->createUrl('/crm/v3/objects/contacts/'.$user->crm_contact_id))
            ->throw()
            ->status();
        $user->update(['crm_contact_id' => null]);

        return 204 === $responseStatus;
    }

    /**
     * @return mixed
     */
    protected function createUrl(string $route): string
    {
        return $this->baseUrl.$route.'?hapikey='.$this->apiKey;
    }

    public function trackDocumentExport(User $user): bool
    {
        if (! $user->crm_contact_id) {
            return false;
        }
        $adapter = $this->getTrackEventAdapter(eventName: 'document-export');
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::post($this->createUrl('/events/v3/send'), $requestBody)
            ->throw()
            ->json();

        return true;
    }
}
