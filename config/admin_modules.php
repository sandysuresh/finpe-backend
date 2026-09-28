<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'route' => 'admin.dashboard',
    ],
    'vendors' => [
        'label' => 'Partners',
        'route' => 'admin.vendors',
    ],
    'wallet-requests' => [
        'label' => 'Wallet Requests',
        'route' => 'admin.wallet-requests',
    ],
    'transactions' => [
        'label' => 'Transactions',
        'route' => 'admin.transactions',
    ],
    'settlements' => [
        'label' => 'Settlements',
        'route' => null,
    ],
    'reports' => [
        'label' => 'Reports',
        'route' => 'admin.reports',
        'children' => [
            ['label' => 'Wallet reports', 'route' => 'admin.reports'],
            ['label' => 'Daily balance', 'route' => 'admin.daily-balance'],
        ],
    ],
    'api-logs' => [
        'label' => 'API Logs',
        'route' => null,
    ],
    'banks' => [
        'label' => 'Banks',
        'route' => 'admin.banks',
    ],
    'users' => [
        'label' => 'Users',
        'route' => 'admin.users',
    ],
];
