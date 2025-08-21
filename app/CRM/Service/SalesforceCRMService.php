<?php

namespace App\CRM\Service;

use function app;
use App\CRM\Adapter\Salesforce\LeadAdapter;
use App\CRM\Service\Auth\AuthTokenProviderInterface;
use App\Events\ExportedDocument;
use App\Models\User;
use Arr;
use AssertionError;
use Exception;
use Http;
use http\Exception\RuntimeException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

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

    private function leadAdapter(): LeadAdapter
    {
        return app()->make(LeadAdapter::class);
    }

    public function handleUserRegistered(Registered $event): bool
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return false;
        }

        $customProperties = [
            'Product_Family__c' => 'ABAS',
            'Status'            => 'Pre Lead',
            'LeadSource'        => 'ERP Planner',
        ];

        return $this->createLead($user, $customProperties)->successful();
    }

    public function handleDocumentExport(ExportedDocument $event): bool
    {
        return true;
    }

    public function createLead(User $user, array $customProperties): string
    {
        $this->logMethod(__METHOD__);

        $data = $this->leadAdapter()->toRequestBody($user, $customProperties);

        $path = $this->path('sobjects', 'Lead');

        $response = $this->request()->post($path, $data);

        $leadId = $this
            ->logResponse($response, "POST $path")
            ->requireSuccess($response, 'create lead')
            ->requireId($response);

        $salesforce = $user->salesforce;
        $salesforce->lead_id = $leadId;
        $user->salesforce->save();

        return $this->requireId($response);
    }

    public function getLead(string $leadId): array
    {
        $this->logMethod(__METHOD__);

        $path = $this->path('sobjects', 'Lead', $leadId);

        $response = $this->request()->get($path);

        $this
            ->logResponse($response, "GET $path")
            ->requireSuccess($response, 'get lead');

        return $response->json();
    }

    private function search(string $query): ?string
    {
        $this->logMethod(__METHOD__);

        $path = $this->path('query');

        $response = $this->request()->get($path, ['q' => $query]);

        $this
            ->logResponse($response, "GET $path")
            ->requireSuccess($response, 'search object');

        return Arr::get($response, 'records.0.Id');
    }

    public function searchLeadyByEmail(string $email): ?string
    {
        $this->logMethod(__METHOD__);

        $query = sprintf("SELECT Id FROM Lead WHERE Email = '%s'", $email);

        return $this->search($query);
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

    public function requireField(Response $response, string $field): mixed
    {
        $value = $this->getField($response, $field);

        if ($value === null) {
            throw new AssertionError(sprintf("Response does not contain non-null '%s': %s", $field, $response->body()));
        }

        return $value;
    }

    public function getField(Response $response, string $field): mixed
    {
        $this->requireSuccess($response, sprintf("Get '%s' on failed response", $field));

        $data = $response->json();

        return Arr::get($data, $field);
    }
}
