<?php

namespace App\CRM\Service;

use function app;
use App\CRM\Adapter\Adapter;
use App\CRM\Adapter\Salesforce\AccountAdapter;
use App\CRM\Adapter\Salesforce\ContactAdapter;
use App\CRM\Adapter\Salesforce\ContentDocumentAdapter;
use App\CRM\Adapter\Salesforce\ContentDocumentLinkAdapter;
use App\CRM\Adapter\Salesforce\ContentVersionAdapter;
use App\CRM\Adapter\Salesforce\LeadAdapter;
use App\CRM\Adapter\Salesforce\TaskAdapter;
use App\CRM\Enums\SalesforceObjectType;
use App\CRM\Enums\SalesforceTaskStatus;
use App\CRM\Enums\SalesforceTaskSubject;
use App\CRM\Service\Auth\AuthTokenProviderInterface;
use App\Events\ExportedDocument;
use App\Http\Resources\SpecificationDocument;
use App\Models\User;
use Arr;
use AssertionError;
use Exception;
use Http;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SalesforceCRMService implements CRMService
{
    /**
     * @param array{'enabled': boolean, 'baseUrl': string, 'clientId': string, 'clientSecret': string} $options
     */
    public function __construct(
        private array $options,
        private AuthTokenProviderInterface $authTokenProvider,
    ) {
        $this->setBasePath('services/data/v63.0');
    }

    private function adapter(SalesforceObjectType $objectType): Adapter
    {
        $class = match ($objectType) {
            SalesforceObjectType::Lead                => LeadAdapter::class,
            SalesforceObjectType::Contact             => ContactAdapter::class,
            SalesforceObjectType::Account             => AccountAdapter::class,
            SalesforceObjectType::Task                => TaskAdapter::class,
            SalesforceObjectType::ContentVersion      => ContentVersionAdapter::class,
            SalesforceObjectType::ContentDocument     => ContentDocumentAdapter::class,
            SalesforceObjectType::ContentDocumentLink => ContentDocumentLinkAdapter::class,
        };

        return app()->make($class);
    }

    public function handleUserRegistered(Registered $event): bool
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return false;
        }

        $data = [
            'Product_Family__c' => 'ABAS',
            'Status'            => 'Pre Lead',
            'LeadSource'        => 'ERP Planner',
        ];

        return $this->createLead($user, $data) !== null;
    }

    public function handleDocumentExport(ExportedDocument $event): bool
    {
        return true;
    }

    public function createLead(User $user, array $data): string
    {
        return $this->createObject($user, SalesforceObjectType::Lead, $data);
    }

    public function getLead(string $leadId): array
    {
        return $this->getObject($leadId, SalesforceObjectType::Lead);
    }

    public function searchLeadBy(string $email): ?string
    {
        return $this->search(
            sprintf("SELECT Id FROM Lead WHERE Email = '%s'", $email),
            SalesforceObjectType::Lead,
        );
    }

    public function updateLead(string $leadId, User $user, array $data): bool
    {
        return $this->updateObject($leadId, $user, SalesforceObjectType::Lead, $data);
    }

    public function createContact(User $user, array $data): string
    {
        return $this->createObject($user, SalesforceObjectType::Contact, $data);
    }

    public function getContact(string $contactId): array
    {
        return $this->getObject($contactId, SalesforceObjectType::Contact);
    }

    public function searchContactBy(string $email): ?string
    {
        return $this->search(
            sprintf("SELECT Id FROM Contact WHERE Email = '%s'", $email),
            SalesforceObjectType::Contact,
        );
    }

    public function updateContact(string $contactId, User $user, array $data): bool
    {
        return $this->updateObject($contactId, $user, SalesforceObjectType::Contact, $data);
    }

    public function createAccount(User $user, array $data): string
    {
        return $this->createObject($user, SalesforceObjectType::Account, $data);
    }

    public function getAccount(string $accountId): array
    {
        return $this->getObject($accountId, SalesforceObjectType::Account);
    }

    public function searchAccountBy(string $name): ?string
    {
        return $this->search(
            sprintf("SELECT Id FROM Account WHERE Name = '%s'", $name),
            SalesforceObjectType::Account,
        );
    }

    public function updateAccount(string $accountId, User $user, array $data): bool
    {
        return $this->updateObject($accountId, $user, SalesforceObjectType::Account, $data);
    }

    public function createTask(User $user, array $data): string
    {
        return $this->createObject($user, SalesforceObjectType::Task, $data);
    }

    public function getTask(string $taskId): array
    {
        return $this->getObject($taskId, SalesforceObjectType::Task);
    }

    public function updateTask(string $taskId, User $user, array $data): bool
    {
        return $this->updateObject($taskId, $user, SalesforceObjectType::Task, $data);
    }

    public function searchTaskBy(SalesforceTaskSubject $subject, string $whoId, SalesforceTaskStatus $status): ?string
    {
        return $this->search(
            sprintf("SELECT Id FROM Task WHERE WhoId = '%s' AND Subject = '%s' AND Status = '%s'", $whoId, $subject->value, $status->value),
            SalesforceObjectType::Task,
        );
    }

    public function createContentVersion(User $user, SpecificationDocument $document, array $data): string
    {
        $data = array_merge($data, [
            'VersionData' => $this->versionData($document),
        ]);

        return $this->createObject($user, SalesforceObjectType::ContentVersion, $data);
    }

    public function getContentVersion(string $contentVersionId): array
    {
        return $this->getObject($contentVersionId, SalesforceObjectType::ContentVersion);
    }

    public function searchContentVersionForContentDocumentBy(string $contentVersionId): ?string
    {
        return $this->search(
            sprintf("SELECT ContentDocumentId FROM ContentVersion WHERE Id = '%s'", $contentVersionId),
            SalesforceObjectType::ContentVersion,
            'ContentDocumentId'
        );
    }

    private function getObject($id, SalesforceObjectType $objectType): array
    {
        $scope = sprintf('get %s ', $objectType->value);

        $this->logMethod($scope);

        $path = $this->path('sobjects', $objectType->value, $id);

        $response = $this->request()->get($path);

        $this
            ->logResponse($response, "GET $path")
            ->requireSuccess($response, $scope);

        return $response->json();
    }

    private function createObject(User $user, SalesforceObjectType $objectType, array $data): string
    {
        $scope = sprintf('create %s ', $objectType->value);

        $this->logMethod($scope);

        $path = $this->path('sobjects', $objectType->value);

        $data = $this->adapter($objectType)->toRequestBody($user, $data);

        $response = $this->request()->post($path, $data);

        $id = $this
            ->logResponse($response, "POST $path")
            ->requireSuccess($response, $scope)
            ->requireId($response);

        $user->salesforce->saveObjectId($id, $objectType);

        return $id;
    }

    private function updateObject(string $id, User $user, SalesforceObjectType $objectType, array $data): bool
    {
        $scope = sprintf('update %s ', $objectType->value);

        $this->logMethod($scope);

        $path = $this->path('sobjects', $objectType->value, $id);

        $data = $this->adapter($objectType)->toRequestBody($user, $data);

        $response = $this->request()->patch($path, $data);

        $this
            ->logResponse($response, "PATCH $path")
            ->requireSuccess($response, $scope);

        $user->salesforce->saveObjectId($id, $objectType);

        return true;
    }

    private function search(string $query, SalesforceObjectType $objectType, string $attribute = 'Id'): ?string
    {
        $this->logMethod(sprintf('search %s', $objectType->value));

        $path = $this->path('query');

        $response = $this->request()->get($path, ['q' => $query]);

        $this
            ->logResponse($response, "GET $path")
            ->requireSuccess($response, 'search object');

        return Arr::get($response, sprintf('records.0.%s', $attribute));
    }

    private function requireSuccess(Response $response, ?string $scope = null): static
    {
        if ($response->failed()) {
            $msg = $scope ? "Failed to $scope" : 'Request failed';
            throw new RuntimeException(sprintf('%s: %s', $msg, $response->body()));
        }

        return $this;
    }

    private function requireId(Response $response): string
    {
        return $this->requireField($response, 'id');
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->options['baseUrl'])
            ->withToken(
                $this->authTokenProvider->provide(),
                $this->authTokenProvider->tokenType(),
            )
            ->retry(3, 200, function (Exception $exception, PendingRequest $request) {
                if ($exception instanceof RequestException) {
                    if ($exception->response->status() === 401) {
                        $this->authTokenProvider->refresh();
                        $request->withToken(
                            $this->authTokenProvider->provide(),
                            $this->authTokenProvider->tokenType(),
                        );
                    } else {
                        return false;
                    }
                }

                return true;
            });
    }

    protected string $basePath = '';

    private function setBasePath(string $basePath): static
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * @param string|int ...$parts
     */
    private function path(...$parts): string
    {
        $separator = '/';

        $parts = array_merge([$this->basePath], $parts);
        $parts = array_filter($parts, static fn ($part) => $part !== '');
        $parts = array_map(static fn ($part) => is_string($part) ? trim($part, " \t\n\r\0\x0B{$separator}") : $part, $parts);

        return implode($separator, $parts);
    }

    private function logMethod(string $method): static
    {
        Log::debug($method);

        return $this;
    }

    private function logResponse(Response $response, string $requestInfo): static
    {
        if ($response->failed()) {
            Log::error($requestInfo, [
                'error'   => $response->toException()?->getMessage(),
                'headers' => $response->headers(),
                'body'    => $response->body(),
            ]);
        } else {
            Log::debug($requestInfo, [
                'headers' => $response->headers(),
                'body'    => $response->body(),
            ]);
        }

        return $this;
    }

    private function requireField(Response $response, string $field): mixed
    {
        $value = $this->getField($response, $field);

        if ($value === null) {
            throw new AssertionError(sprintf("Response does not contain non-null '%s': %s", $field, $response->body()));
        }

        return $value;
    }

    private function getField(Response $response, string $field): mixed
    {
        $this->requireSuccess($response, sprintf("Get '%s' on failed response", $field));

        $data = $response->json();

        return Arr::get($data, $field);
    }

    private function versionData(SpecificationDocument $document): string
    {
        $path = $document->outputExcelFilename();

        if (! file_exists($path)) {
            throw new RuntimeException("Specification document does not exist: $path");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Failed to read specification document: $path");
        }

        $versionData = base64_encode($contents);

        return $versionData;
    }
}
