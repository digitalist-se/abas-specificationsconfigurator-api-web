<?php

use App\CRM\Enums\HubSpotEventType;

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'hubSpot' => [
        'enabled'     => env('HUBSPOT_ENABLED', true),
        'baseUrl'     => env('HUBSPOT_API_BASE_URL', 'https://api.hubapi.com'),
        'apiKey'      => env('HUBSPOT_API_KEY'),
        'accessToken' => env('HUBSPOT_PRIVATE_APP_ACCESS_TOKEN'),
        'events'      => [
            HubSpotEventType::DocumentExport->value => 'pe2853580_lastenheft_erstellung',
            HubSpotEventType::UserRegistered->value => 'pe2853580_registrierung_auf_erp_planner',
        ],
        'folder' => [
            'id' => env('HUBSPOT_FOLDER_ID'),
        ],
    ],

    'salesforce' => [
        'enabled'      => env('SALESFORCE_ENABLED', false),
        'baseUrl'      => env('SALESFORCE_BASEURL'),
        'clientId'     => env('SALESFORCE_CLIENT_ID'),
        'clientSecret' => env('SALESFORCE_CLIENT_SECRET'),
    ],
];
