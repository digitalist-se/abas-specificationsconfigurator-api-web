<?php

namespace App\CRM\Service;

use function app;
use App\CRM\Adapter\Adapter;
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
use Arr;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;

class HubSpotCRMService implements CRMService
{
    protected ?string $baseUrl = null;

    protected ?string $accessToken = null;

    protected ?string $folderId = null;

    /**
     * @var array<string, string>
     */
    protected array $events = [];

    public function __construct($options = [])
    {
        if (isset($options['accessToken'])) {
            $this->accessToken = $options['accessToken'];
        }
        if (isset($options['baseUrl'])) {
            $this->baseUrl = $options['baseUrl'];
        }
        if (isset($options['events'])) {
            $this->events = $options['events'];
        }
        $this->folderId = $options['folder']['id'] ?? null;
    }

    protected function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    protected function request(): PendingRequest
    {
        $accessToken = $this->getAccessToken();

        if (empty($accessToken)) {
            $accessToken = 'missing_token';
            Log::error('Required access token of private app is not given.');
        }

        return Http::withToken($accessToken);
    }

    protected function getCompanyAdapter(): CompanyAdapter
    {
        return app()->make(CompanyAdapter::class);
    }

    /**
     * @return UserContactAdapter|CompanyContactAdapter
     */
    protected function getContactAdapter(ContactType $type): Adapter
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

    public function handleUserRegistered(Registered $event): bool
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return false;
        }

        $this->upsertContact($user, ContactType::User, ['erp_registration_trigger' => true]);
        $this->trackUserRegistered($event);

        return true;
    }

    public function handleDocumentExport(ExportedDocument $event): bool
    {
        $this->upsertContact($event->user, ContactType::User, ['erp_lastenheft_trigger' => true]);
        $this->updateCompany($event->user);
        $this->upsertContact($event->user, ContactType::Company, []);

        $this->trackDocumentExport($event);

        return true;
    }

    public function createContact(User $user, ContactType $type, array $customProperties = []): bool
    {
        $this->logMethod(__METHOD__);

        $adapter = $this->getContactAdapter($type);
        $requestBody = $adapter->toCreateRequestBody($user, $customProperties);
        $url = $this->createUrl('/crm/v3/objects/contacts');
        $response = $this->request()->post($url, $requestBody);

        $this->logResponse($url, $response);

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
        $this->logMethod(__METHOD__);

        $companyId = $this->getContactCompanyID($user, ContactType::User);

        if (empty($companyId)) {
            $this->logError('missing company id', [$user, $companyId]);

            return false;
        }

        $adapter = $this->getCompanyAdapter();
        $requestBody = $adapter->toCreateRequestBody($user);
        $url = $this->createUrl('/crm/v3/objects/companies/'.$companyId);
        $response = $this->request()->patch($url, $requestBody);

        $this->logResponse($url, $response);

        return $response->successful();
    }

    public function updateContact(User $user, ContactType $type, array $customProperties = []): bool
    {
        $this->logMethod(__METHOD__);

        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            $this->logError('missing contact id', [$user, $type]);

            return false;
        }

        $adapter = $this->getContactAdapter($type);
        $requestBody = $adapter->toCreateRequestBody($user, $customProperties);
        $url = $this->createUrl('/crm/v3/objects/contacts/'.$crmContactId);
        $response = $this->request()->patch($url, $requestBody);

        $this->logResponse($url, $response);

        return $response->successful();
    }

    public function upsertContact(User $user, ContactType $type, array $customProperties = []): bool
    {
        $this->logMethod(__METHOD__);

        if (empty($user->getCrmContactId($type))) {
            if ($crmContactId = $this->getContactIDByEmail($user, $type)) {
                $user->setCrmContactId($type, $crmContactId)
                    ->save();
            }
        }

        return empty($user->getCrmContactId($type))
            ? $this->createContact($user, $type, $customProperties)
            : $this->updateContact($user, $type, $customProperties);
    }

    public function deleteContact(User $user, ContactType $type): bool
    {
        $this->logMethod(__METHOD__);

        $crmContactId = $user->getCrmContactId($type);

        if (empty($crmContactId)) {
            $this->logError('missing contact id', [$user, $type]);

            return false;
        }

        $url = $this->createUrl('/crm/v3/objects/contacts/'.$crmContactId);
        $response = $this->request()->delete($url);

        $this->logResponse($url, $response);

        if ($response->failed()) {
            return false;
        }

        $user->setCrmContactId($type, null)
            ->save();

        return 204 === $response->status();
    }

    public function trackDocumentExport(ExportedDocument $event): bool
    {
        $this->logMethod(__METHOD__);

        $user = $event->user;
        if (! $user->crm_user_contact_id) {
            $this->logError('missing contact id', [$event]);

            return false;
        }

        $this->createEvent(HubSpotEventType::DocumentExport, $user);

        $file = $event->document->outputZipFilename();
        $fileId = $this->uploadFile($file);
        if (! $file) {
            $this->logError('track document export, file upload not successful', [$event]);

            return false;
        }

        $this->createNote($user, $fileId, $this->renderUserNote($user));

        return true;
    }

    public function trackUserRegistered(Registered $event): bool
    {
        $this->logMethod(__METHOD__);

        /** @var \App\Models\User $user */
        $user = $event->user;
        $user->refresh();
        if (! $user->crm_user_contact_id) {
            $this->logError('missing contact id', [$event]);

            return false;
        }

        return $this->createEvent(HubSpotEventType::UserRegistered, $user);
    }

    /**
     * @return mixed
     */
    protected function createUrl(string $route): string
    {
        return $this->getBaseUrl().$route;
    }

    protected function createEvent(HubSpotEventType $eventType, User $user): bool
    {
        $this->logMethod(__METHOD__);

        $adapter = $this->getTrackEventAdapter($eventType);
        $requestBody = $adapter->toCreateRequestBody($user);
        $url = $this->createUrl('/events/v3/send');
        $response = $this->request()->withBody(json_encode($requestBody), 'application/json')
            ->post($url);

        $this->logResponse($url, $response);

        if ($response->failed()) {
            Log::error('crm error:'.$response->body());

            return false;
        }

        return true;
    }

    protected function uploadFile($file): ?string
    {
        $this->logMethod(__METHOD__);

        $fileName = basename($file);

        try {
            $options = json_encode([
                'access'                      => 'PRIVATE',
                'overwrite'                   => false,
                'duplicateValidationStrategy' => 'NONE',
                'duplicateValidationScope'    => 'EXACT_FOLDER',
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->logError('json encode for file upload options not successful: '.$e->getMessage());

            return null;
        }

        $url = $this->createUrl('/files/v3/files');
        $response = $this->request()->attach('file', file_get_contents($file), $fileName)
            ->asMultipart()
            ->post($url, [
                'folderId' => $this->folderId,
                'options'  => $options,
            ]);

        $this->logResponse($url, $response);

        if (! $response->successful()) {
            $this->logError('file upload not successful');

            return null;
        }

        return $response->json('id');
    }

    protected function createNote(User $user, $fileId, $body): bool
    {
        $this->logMethod(__METHOD__);

        $contactCompanyID = $this->getContactCompanyID($user, ContactType::User);
        $associations = collect([
            'contactIds' => [$user->crm_user_contact_id, $user->crm_company_contact_id],
            'companyIds' => [$contactCompanyID],
            'dealIds'    => $this->getDealIDsForCompany($contactCompanyID),
        ])
            ->map(fn ($ids) => collect($ids)->filter()->toArray())
            ->toArray();

        $adapter = $this->getEngagementAdapter();
        $requestBody = $adapter->toCreateRequestBody($fileId, $body, $associations);
        $url = $this->createUrl('/engagements/v1/engagements');
        $response = $this->request()->post($url, $requestBody);

        $this->logResponse($url, $response);

        return $response->successful();
    }

    protected function searchContactsByEmail(User $user, ContactType $type): ?array
    {
        $this->logMethod(__METHOD__);

        $crmEmail = $user->getCrmEmail($type);
        if (! $crmEmail) {
            return null;
        }

        $body = [
            'filterGroups' => [
                [
                    'filters' => [
                        [
                            'value'        => $crmEmail,
                            'propertyName' => 'email',
                            'operator'     => 'EQ',
                        ],
                    ],
                ],
            ],
        ];
        $url = $this->createUrl('/crm/v3/objects/contacts/search');
        $response = $this->request()->post($url, $body);

        $this->logResponse($url, $response);

        if ($response->failed()) {
            return null;
        }

        return $response->json('results');
    }

    protected function getContactIDByEmail(User $user, ContactType $type): ?string
    {
        $this->logMethod(__METHOD__);

        $results = $this->searchContactsByEmail($user, $type);

        if ($results) {
            return Arr::get($results, '0.id');
        }

        return null;
    }

    protected function getContactCompanyID(User $user, ContactType $type): ?string
    {
        $this->logMethod(__METHOD__);

        $crmContactId = $user->getCrmContactId($type);
        if (empty($crmContactId)) {
            $this->logError('missing contact id', [$user, $type]);

            return null;
        }

        $url = $this->createUrl('/crm/v3/objects/contacts/'.$crmContactId.'/associations/company');
        $response = $this->request()->get($url);

        $this->logResponse($url, $response);

        if ($response->failed()) {
            return null;
        }

        return $response->json('results.0.id');
    }

    /**
     * @return array<string>|null
     */
    protected function getDealIDsForCompany(?string $companyId): ?array
    {
        $this->logMethod(__METHOD__);

        if (empty($companyId)) {
            return null;
        }

        $url = $this->createUrl('/crm/v3/objects/companies/'.$companyId.'/associations/deal');
        $response = $this->request()->get($url);

        $this->logResponse($url, $response);

        if ($response->failed()) {
            return null;
        }

        return $response->json('results.*.id');
    }

    protected function renderUserNote(User $user): string
    {
        return $this->getUserNoteAdapter()
            ->createNoteBody($user);
    }

    protected function logMethod(string $method)
    {
        Log::debug($method);
    }

    protected function logResponse(string $url, Response $response)
    {
        if ($response->failed()) {
            Log::error($url, [
                'error'   => $response->toException()?->getMessage(),
                'headers' => $response->headers(),
                'body'    => $response->body(),
            ]);
        } else {
            Log::debug($url, [
                'headers' => $response->headers(),
                'body'    => $response->body(),
            ]);
        }
    }

    protected function logError(string $message, array $context = [])
    {
        Log::error($message, $context);
    }
}
