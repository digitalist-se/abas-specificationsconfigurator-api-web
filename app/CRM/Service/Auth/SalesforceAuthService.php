<?php

namespace App\CRM\Service\Auth;

use Illuminate\Support\Facades\Http;

class SalesforceAuthService
{
    /**
     * @param array{'enabled': boolean, 'baseUrl': string, 'clientId': string, 'clientSecret': string} $options
     */
    public function __construct(private array $options = [])
    {
    }

    /**
     * @return array{
     *   access_token: string,
     *   signature: string,
     *   scope: string,
     *   instance_url: string,
     *   id: string,
     *   token_type: string,
     *   issued_at: string
     * }
     */
    public function token(): array
    {
        $response = Http::baseUrl($this->options['baseUrl'])
            ->asForm()
            ->post('/services/oauth2/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->options['clientId'],
                'client_secret' => $this->options['clientSecret'],
            ]);

        return $response->json();
    }
}
