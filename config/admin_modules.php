<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'route' => 'admin.dashboard',
    ],
    'app-users' => [
        'label' => 'Users',
        'route' => 'admin.app-users.list',
        'inherits' => 'dashboard',
        'children' => [
            ['label' => 'All Users', 'route' => 'admin.app-users.list'],
            ['label' => 'User Details', 'route' => 'admin.app-users.details'],
            ['label' => 'KYC Verification', 'route' => 'admin.app-users.kyc'],
            ['label' => 'User History', 'route' => 'admin.app-users.history'],
            ['label' => 'Login / Activity History', 'route' => 'admin.app-users.activity'],
            ['label' => 'Block / Unblock User', 'route' => 'admin.app-users.block'],
        ],
    ],
    'vendors' => [
        'label' => 'Partners',
        'route' => 'admin.vendors',
        'children' => [
            ['label' => 'Partner List', 'route' => 'admin.vendors'],
            ['label' => 'Add Partner', 'route' => 'admin.vendors.create'],
            ['label' => 'Partner KYC', 'route' => 'admin.partners.kyc'],
            ['label' => 'Partner Wallet', 'route' => 'admin.partners.wallet'],
            ['label' => 'Partner Transactions', 'route' => 'admin.partners.transactions'],
            ['label' => 'Partner Commission', 'route' => 'admin.partners.commission'],
            ['label' => 'Partner Settlement', 'route' => 'admin.partners.settlement'],
        ],
    ],
    'merchants' => [
        'label' => 'Merchants',
        'route' => 'admin.merchants',
        'inherits' => 'vendors',
    ],
    'transactions' => [
        'label' => 'Transactions',
        'route' => 'admin.transactions',
        'children' => [
            ['label' => 'All Transactions', 'route' => 'admin.transactions'],
            ['label' => 'Transaction History', 'route' => 'admin.txn-history'],
            ['label' => 'Successful Transactions', 'route' => 'admin.txn-success'],
            ['label' => 'Pending Transactions', 'route' => 'admin.txn-pending'],
            ['label' => 'Failed Transactions', 'route' => 'admin.txn-failed'],
            ['label' => 'Reversed / Refund', 'route' => 'admin.txn-reversed'],
            ['label' => 'Search & Filter', 'route' => 'admin.txn-search'],
            ['label' => 'Transaction Details', 'route' => 'admin.txn-details'],
            ['label' => 'AePS Transactions', 'route' => 'admin.aeps-transactions'],
            ['label' => 'Payout Transactions', 'route' => 'admin.payout-transactions'],
        ],
    ],
    'commission' => [
        'label' => 'Commission',
        'route' => 'admin.commission.rules',
        'children' => [
            ['label' => 'Commission Rules', 'route' => 'admin.commission.rules'],
            ['label' => 'Commission History', 'route' => 'admin.commission.history'],
            ['label' => 'Agent Commission', 'route' => 'admin.commission.partners'],
            ['label' => 'Merchant Commission', 'route' => 'admin.commission.merchants'],
            ['label' => 'Commission Settlement', 'route' => 'admin.commission.settlement'],
        ],
    ],
    'wallet-requests' => [
        'label' => 'Wallet',
        'route' => 'admin.wallet-requests',
        'children' => [
            ['label' => 'Wallet Requests', 'route' => 'admin.wallet-requests'],
        ],
    ],
    'reports' => [
        'label' => 'Reports',
        'route' => 'admin.reports',
        'children' => [
            ['label' => 'Wallet reports', 'route' => 'admin.reports'],
            ['label' => 'Daily balance', 'route' => 'admin.daily-balance'],
        ],
    ],
    'payout-api' => [
        'label' => 'Payout API',
        'route' => 'admin.payout-api',
    ],
    'banks' => [
        'label' => 'Bank & API',
        'route' => 'admin.banks',
        'children' => [
            ['label' => 'Banks', 'route' => 'admin.banks'],
            ['label' => 'API Logs', 'route' => 'admin.api-logs'],
        ],
    ],
    'users' => [
        'label' => 'Administration',
        'route' => 'admin.users',
        'children' => [
            ['label' => 'Admin Users', 'route' => 'admin.users'],
            ['label' => 'Roles', 'route' => 'admin.roles'],
            ['label' => 'Permissions', 'route' => 'admin.permissions'],
            ['label' => 'Approval Workflow', 'route' => 'admin.approval-workflow'],
            ['label' => 'Audit Logs', 'route' => 'admin.audit-logs'],
            ['label' => 'System Settings', 'route' => 'admin.system-settings'],
        ],
    ],
];
