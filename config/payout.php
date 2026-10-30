<?php

return [
    'provider' => env('PAYOUT_PROVIDER', 'vimopay'),

    'providers' => [
        'vimopay' => App\Services\Payout\VimoPayPayoutTransfer::class,
    ],

    'live_endpoints' => [
        'payout_authorize' => 'https://prod.vidual.in/payoutapi/api/signature/authorize',
        'payout_transfer' => 'https://prod.vidual.in/payoutapi/api/Payment/payout',
        'payout_purposes' => 'https://prod.vidual.in/masterapi/api/master/purposelist',
        'payout_banks' => 'https://prod.vidual.in/masterapi/api/master/banklist',
        'payout_states' => 'https://prod.vidual.in/masterapi/api/master/statelist',
    ],
];

