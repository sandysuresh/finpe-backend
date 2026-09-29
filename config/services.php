<?php

return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'resend' => ['key' => env('RESEND_KEY')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'vimopay' => [
        'base_url' => env('VIMOPAY_BASE_URL'),
        'user_id' => env('VIMOPAY_USER_ID'),
        'secret_key' => env('VIMOPAY_SECRET_KEY'),
        'salt_key' => env('VIMOPAY_SALT_KEY'),
        'encrypt_key' => env('VIMOPAY_ENCRYPT_KEY'),
        'iv_key' => env('VIMOPAY_IV_KEY'),
        'environment' => env('VIMOPAY_ENV', 'uat'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
];