<?php

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
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'keycloak' => [
        'enabled' => env('KEYCLOAK_ENABLED', false),
        'client_id' => env('KEYCLOAK_CLIENT_ID'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect' => env('KEYCLOAK_REDIRECT_URI'),
        'base_url' => env('KEYCLOAK_BASE_URL'),
        'realms' => env('KEYCLOAK_REALM'),
        'btn_label' => env('KEYCLOAK_BTN_LABEL', 'Keycloak'),
        // Fallback to local user if no user found in Keycloak
        'fallback_local' => env('KEYCLOAK_FALLBACK_LOCAL', false),
        'auto_provision' => env('KEYCLOAK_AUTO_PROVISION', false),
        'auto_provision_role' => env('KEYCLOAK_AUTO_PROVISION_ROLE', 'User'),
        'sync_roles' => env('KEYCLOAK_SYNC_ROLES', false),
    ],

    'nvd' => [
        'api_url' => env('NVD_API_URL', 'https://services.nvd.nist.gov/rest/json/cpes/2.0'),
        'api_key' => env('NVD_API_KEY'), // optionnel
    ],

    'cpe_guesser' => [
        'url' => env('CPE_GUESSER_URL', 'https://cpe-guesser.cve-search.org'),
        'endpoint' => env('CPE_GUESSER_ENDPOINT', 'search'), // "search" ou "unique"
        'timeout' => env('CPE_GUESSER_TIMEOUT', 6),
    ],
];
