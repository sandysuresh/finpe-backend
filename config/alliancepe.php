<?php

return [
    'environment' => env('ALLIANCEPE_ENV', 'uat'),

    'connect_timeout' => (int) env('ALLIANCEPE_CONNECT_TIMEOUT', 10),

    'timeout' => (int) env('ALLIANCEPE_REQUEST_TIMEOUT', 30),

    /*
     * Public HTTPS URL AlliancePe calls for payout status updates.
     * The path must stay /api/webhooks/alliancepe/payouts.
     */
    'callback_url' => env('ALLIANCEPE_CALLBACK_URL'),

    'uat' => [
        'base_url' => env('ALLIANCEPE_UAT_BASE_URL'),
        'api_key' => env('ALLIANCEPE_UAT_API_KEY'),
        'webhook_secret' => env('ALLIANCEPE_UAT_WEBHOOK_SECRET'),
    ],

    'live' => [
        'base_url' => env('ALLIANCEPE_LIVE_BASE_URL'),
        'api_key' => env('ALLIANCEPE_LIVE_API_KEY'),
        'webhook_secret' => env('ALLIANCEPE_LIVE_WEBHOOK_SECRET'),
    ],
];
