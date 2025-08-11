<?php

namespace App\CRM\Service;

use function app;
use App\CRM\Adapter\Salesforce\LeadAdapter;
use App\CRM\Service\Auth\AuthTokenProviderInterface;
use App\Events\ExportedDocument;
use App\Models\User;
use Exception;
use Http;
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

        return $this->createLead($user)->successful();
    }

    public function handleDocumentExport(ExportedDocument $event): bool
    {
        return true;
    }

    public function getLead(string $leadId): Response
    {
        $this->logMethod(__METHOD__);

        $response = $this->request()
            ->get('/services/data/v63.0/sobjects/Lead/'.$leadId);

        $this->logResponse($response->effectiveUri(), $response);

        return $response;
    }

    public function createLead(User $user): Response
    {
        $this->logMethod(__METHOD__);

        $leadData = $this->leadAdapter()->toCreateRequestBody($user, [
            'Product_Family__c' => 'ABAS',
            'Status'            => 'Pre Lead',
            'LeadSource'        => 'ERP Planner',
        ]);
        $response = $this->request()
            ->post('/services/data/v63.0/sobjects/Lead', $leadData);

        $this->logResponse($response->effectiveUri(), $response);

        return $response;
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

    private function logMethod(string $method)
    {
        Log::debug($method);
    }

    private function logResponse(string $url, Response $response)
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
}
