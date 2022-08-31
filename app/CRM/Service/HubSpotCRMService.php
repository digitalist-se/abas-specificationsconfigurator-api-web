<?php

namespace App\CRM\Service;

use App\CRM\Adapter\CompanyAdapter;
use App\CRM\Adapter\CompanyContactAdapter;
use App\CRM\Adapter\EngagementNoteAdapter;
use App\CRM\Adapter\TrackEventAdapter;
use App\CRM\Adapter\UserContactAdapter;
use App\CRM\Adapter\UserNoteAdapter;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function abort;
use function app;

class HubSpotCRMService implements CRMService
{
    protected ?string $baseUrl = null;

    protected ?string $apiKey = null;

    protected ?string $folderId = null;

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
        $this->folderId = $options['folder']['id'] ?? null;
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

    protected function getContactAdapter(ContactType $type): UserContactAdapter
    {
        $class = match ($type) {
            ContactType::User    => UserContactAdapter::class,
            ContactType::Company => CompanyContactAdapter::class,
        };

        return app()->make($class);
    }

    protected function getEngagementAdapter(): EngagementNoteAdapter
    {
        return app()->make(EngagementNoteAdapter::class);
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
            foreach (ContactType::cases() as $type) {
                $this->linkContactsToCompany($user, $type);
            }
        }

        return true;
    }

    public function createContact(User $user, ContactType $type): bool
    {
        $adapter = $this->getContactAdapter($type);
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::post($this->createUrl('/crm/v3/objects/contacts'), $requestBody)
            ->throw()
            ->json();
        if (isset($response['id'])) {
            $contactId = $response['id'];
            $user->update([
                $user->getCrmContactIdKey($type) => $contactId,
            ]);
            $this->linkContactsToCompany($user, $type);
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

    public function updateContact(User $user, ContactType $type): bool
    {
        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            abort(500, 'missing contact id');
        }

        $adapter = $this->getContactAdapter($type);
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::put($this->createUrl('/crm/v3/objects/contacts/'.$crmContactId), $requestBody)
            ->throw()
            ->json();
        if (isset($response['id'])) {
            $companyId = $response['id'];
            $user->update([
                $user->getCrmContactIdKey($type) => $companyId,
            ]);
        }

        return true;
    }

    public function linkContactsToCompany(User $user, ContactType $type): bool
    {
        $crmCompanyId = $user->crm_company_id;
        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmCompanyId) || empty($crmContactId)) {
            return false;
        }

        $routeParts = [
            '/crm/v3/objects/contacts/',
            $crmContactId,
            '/associations/companies/',
            $crmCompanyId,
            '/contact_to_company',
        ];

        $response = Http::put($this->createUrl(Arr::join($routeParts, '')), [])
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

    public function deleteContact(User $user, ContactType $type): bool
    {
        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            abort(500, 'missing contact id');
        }

        $responseStatus = Http::delete($this->createUrl('/crm/v3/objects/contacts/'.$user->crm_user_contact_id))
            ->throw()
            ->status();
        $user->update(['crm_user_contact_id' => null]);

        return 204 === $responseStatus;
    }

    /**
     * @return mixed
     */
    protected function createUrl(string $route): string
    {
        return $this->baseUrl.$route.'?hapikey='.$this->apiKey;
    }

    public function trackDocumentExport(ExportedDocument $event): bool
    {
        $user = $event->user;
        if (! $user->crm_user_contact_id) {
            return false;
        }
        $this->createExportEvent($user);

        $file = $event->document->outputZipFilename();
        $fileId = $this->uploadFile($file);

        $this->createNote($user, $fileId, $this->renderUserNote($user));

        return true;
    }

    /**
     * @param \App\Models\User $user
     *
     * @return bool
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function createExportEvent(User $user): bool
    {
        $adapter = $this->getTrackEventAdapter(eventName: 'document-export');
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::withBody(json_encode($requestBody), 'application/json')
            ->post($this->createUrl('/events/v3/send'));

        if ($response->ok()) {
            return true;
        }

        Log::error('crm error:'.$response->body());

        return false;
    }

    protected function uploadFile($file)
    {
        $fileName = basename($file);

        $fileResponse = Http::attach('file', file_get_contents($file), $fileName)
            ->asMultipart()
            ->post($this->createUrl('/files/v3/files'), [
                'folderId' => $this->folderId,
                'options'  => json_encode([
                    'access'                      => 'PRIVATE',
                    'overwrite'                   => false,
                    'duplicateValidationStrategy' => 'none',
                    'duplicateValidationScope'    => 'EXACT_FOLDER',
                ]),
            ]);
        if (! $fileResponse->ok()) {
            return false;
        }

        return $fileResponse->json('id');
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function createNote(User $user, $fileId, $body): ?array
    {
        $requestBody = $this->getEngagementAdapter()->toCreateRequestBody($user, $fileId, $body);

        return Http::post($this->createUrl('/engagements/v1/engagements'), $requestBody)
            ->json();
    }

    protected function renderUserNote(User $user): string
    {
        return app()
            ->make(UserNoteAdapter::class)
            ->createNote($user);
    }
}
