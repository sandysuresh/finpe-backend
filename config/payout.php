<?php

return [
    'provider' => env('PAYOUT_PROVIDER', 'vimopay'),

    'providers' => [
        'vimopay' => App\Services\Payout\VimoPayPayoutTransfer::class,
    ],

    'live_endpoints' => [
        'payout_authorize' => env('VIMOPAY_LIVE_PAYOUT_AUTHORIZE_URL', 'https://prod.vidual.in/payoutapi/api/signature/authorize'),
        'payout_transfer' => env('VIMOPAY_LIVE_PAYOUT_TRANSFER_URL', 'https://prod.vidual.in/payoutapi/api/Payment/payout'),
        'payout_purposes' => env('VIMOPAY_LIVE_PAYOUT_PURPOSES_URL', 'https://prod.vidual.in/masterapi/api/master/purposelist'),
        'payout_banks' => env('VIMOPAY_LIVE_PAYOUT_BANKS_URL', 'https://prod.vidual.in/masterapi/api/master/banklist'),
        'payout_states' => env('VIMOPAY_LIVE_PAYOUT_STATES_URL', 'https://prod.vidual.in/masterapi/api/master/statelist'),
    ],

    'uat_endpoints' => [
        'authorize' => env('VIMOPAY_UAT_AUTHORIZE_URL', '/aepsapi/api/signature/authorizeuat'),
        'payout_authorize' => env('VIMOPAY_UAT_PAYOUT_AUTHORIZE_URL', '/aepsapi/api/signature/authorizeuat'),
        'banks' => env('VIMOPAY_UAT_BANKS_URL', '/masterapi/api/master/banklistuat'),
        'bank_iin' => env('VIMOPAY_UAT_BANK_IIN_URL', '/aepsapi/api/payment/bankiinuat'),
        'states' => env('VIMOPAY_UAT_STATES_URL', '/masterapi/api/master/statelistuat'),
        'payout_banks' => env('VIMOPAY_UAT_PAYOUT_BANKS_URL', '/masterapi/api/master/banklistuat'),
        'payout_purposes' => env('VIMOPAY_UAT_PAYOUT_PURPOSES_URL', '/masterapi/api/master/purposelistuat'),
        'payout_states' => env('VIMOPAY_UAT_PAYOUT_STATES_URL', '/masterapi/api/master/statelistuat'),
        'payout_transfer' => env('VIMOPAY_UAT_PAYOUT_TRANSFER_URL', '/payoutapi/api/payment/payoutsuat'),
        'districts' => env('VIMOPAY_UAT_DISTRICTS_URL', '/aepsapi/api/payment/acquiredistrictuat'),
        'register' => env('VIMOPAY_UAT_REGISTER_URL', '/aepsapi/api/payment/merchantonboarduat'),
        'send_otp' => env('VIMOPAY_UAT_SEND_OTP_URL', '/aepsapi/api/payment/sendotpuat'),
        'resend_otp' => env('VIMOPAY_UAT_RESEND_OTP_URL', '/aepsapi/api/payment/resendotpuat'),
        'verify_otp' => env('VIMOPAY_UAT_VERIFY_OTP_URL', '/aepsapi/api/payment/validateotpuat'),
        'ekyc' => env('VIMOPAY_UAT_EKYC_URL', '/aepsapi/api/payment/merchantekycuat'),
        'two_factor' => env('VIMOPAY_UAT_TWO_FACTOR_URL', '/aepsapi/api/payment/2fauat'),
        'transaction_otp' => env('VIMOPAY_UAT_TRANSACTION_OTP_URL', '/aepsapi/api/Payment/AepsTransactionOtpuat'),
        'transact' => env('VIMOPAY_UAT_TRANSACT_URL', '/aepsapi/api/payment/aepsuat'),
    ],
];

