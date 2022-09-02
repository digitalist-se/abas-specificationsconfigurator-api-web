<?php

namespace App\CRM\Service;

use App\CRM\Adapter\CompanyAdapter;
use App\CRM\Adapter\CompanyContactAdapter;
use App\CRM\Adapter\EngagementNoteAdapter;
use App\CRM\Adapter\TrackEventAdapter;
use App\CRM\Adapter\UserContactAdapter;
use App\CRM\Adapter\UserNoteAdapter;
use App\CRM\Enums\HubSpotEventType;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
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

    protected function getUserNoteAdapter(): UserNoteAdapter
    {
        return app()->make(UserNoteAdapter::class);
    }

    protected function getTrackEventAdapter(HubSpotEventType $eventType): TrackEventAdapter
    {
        $eventName = $this->events[$eventType->value] ?? $eventType->value;
        return app()->make(TrackEventAdapter::class, ['eventName' => $eventName]);
    }

    public function createContact(User $user, ContactType $type): bool
    {
        $adapter = $this->getContactAdapter($type);
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::post($this->createUrl('/crm/v3/objects/contacts'), $requestBody);
        if ($response->failed()) {
            return false;
        }

        if ($contactId = $response->json('id')) {
            $user->setCrmContactId($type, $contactId)
                ->save();
        }

        return true;
    }

    public function updateCompany(User $user): bool
    {
        $companyId = $this->getContactCompanyID($user, ContactType::User);

        if (empty($companyId)) {
            return false;
        }

        $adapter = $this->getCompanyAdapter();
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::put($this->createUrl('/crm/v3/objects/companies/'.$companyId), $requestBody);

        return $response->successful();
    }

    public function updateContact(User $user, ContactType $type): bool
    {
        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            abort(500, 'missing contact id');
        }

        $adapter = $this->getContactAdapter($type);
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::put($this->createUrl('/crm/v3/objects/contacts/'.$crmContactId), $requestBody);

        return $response->successful();
    }

    public function upsertContact(User $user, ContactType $type): bool
    {
        return empty($user->getCrmContactId($type))
            ? $this->createContact($user, $type)
            : $this->updateContact($user, $type);
    }

    public function deleteContact(User $user, ContactType $type): bool
    {
        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            return false;
        }

        $response = Http::delete($this->createUrl('/crm/v3/objects/contacts/'.$crmContactId));

        if ($response->failed()) {
            return false;
        }

        $user->setCrmContactId($type, null)
            ->save();

        return 204 === $response->status();
    }

    public function trackDocumentExport(ExportedDocument $event): bool
    {
        $user = $event->user;
        if (! $user->crm_user_contact_id) {
            return false;
        }
        $this->createEvent(HubSpotEventType::DocumentExport, $user);

        $file = $event->document->outputZipFilename();
        $fileId = $this->uploadFile($file);

        $this->createNote($user, $fileId, $this->renderUserNote($user));

        return true;
    }

    public function trackUserRegistered(Registered $event): bool
    {
        /** @var \App\Models\User $user */
        $user = $event->user;
        $user->refresh();
        if (! $user->crm_user_contact_id) {
            return false;
        }

        return $this->createEvent(HubSpotEventType::UserRegistered, $user);
    }

    /**
     * @return mixed
     */
    protected function createUrl(string $route): string
    {
        return $this->getBaseUrl().$route.'?hapikey='.$this->getApiKey();
    }

    private function createEvent(HubSpotEventType $eventType, User $user): bool
    {
        $adapter = $this->getTrackEventAdapter($eventType);
        $requestBody = $adapter->toCreateRequestBody($user);

        $response = Http::withBody(json_encode($requestBody), 'application/json')
            ->post($this->createUrl('/events/v3/send'));

        if ($response->failed()) {
            Log::error('crm error:'.$response->body());

            return false;
        }

        return true;
    }

    /**
     * @throws \JsonException
     */
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
                ], JSON_THROW_ON_ERROR),
            ]);

        if (! $fileResponse->ok()) {
            return false;
        }

        return $fileResponse->json('id');
    }

    protected function createNote(User $user, $fileId, $body): bool
    {
        $adapter = $this->getEngagementAdapter();
        $requestBody = $adapter->toCreateRequestBody($user, $fileId, $body);

        $response = Http::post($this->createUrl('/engagements/v1/engagements'), $requestBody);

        return $response->successful();
    }

    protected function getContactCompanyID(User $user, ContactType $type): ?string
    {
        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            return null;
        }

        $response = Http::get($this->createUrl('/crm/v3/objects/contacts/'.$crmContactId.'/associations/company'));

        if ($response->failed()) {
            return null;
        }

        return $response->json('results.0.id');
    }

    protected function renderUserNote(User $user): string
    {
        return $this->getUserNoteAdapter()
            ->createNote($user);
    }
}
