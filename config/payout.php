<?php

return [
    'provider' => env('PAYOUT_PROVIDER', 'vimopay'),

    'providers' => [
        'vimopay' => App\Services\Payout\VimoPayPayoutTransfer::class,
    ],
];
