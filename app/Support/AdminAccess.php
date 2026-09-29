<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Role → module → action access from the admin overview (sections 2–4).
 */
class AdminAccess
{
    public const ACTIONS = [
        'view' => 'View',
        'search' => 'Search',
        'create' => 'Create',
        'edit' => 'Edit',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'block' => 'Block',
        'unblock' => 'Unblock',
        'export' => 'Export',
        'refund' => 'Refund',
        'reverse' => 'Reverse',
        'settlement' => 'Settlement',
        'change_commission' => 'Change Commission',
        'change_limits' => 'Change Transaction Limits',
    ];

    public static function modules(): array
    {
        $configured = [];
        try {
            $configured = AdminModules::all();
        } catch (\Throwable) {
            $configured = [];
        }

        return self::merge(is_array($configured) ? $configured : []);
    }

    public static function merge(array $configured): array
    {
        $defined = self::definedModules();
        $modules = [];

        foreach ($configured as $key => $meta) {
            if (! is_array($meta) || ! empty($meta['inherits'])) {
                continue;
            }

            $modules[$key] = $defined[$key] ?? [
                'label' => $meta['label'] ?? $key,
                'actions' => ['view', 'search', 'create', 'edit', 'export'],
            ];
            if (! empty($meta['label'])) {
                $modules[$key]['label'] = $meta['label'];
            }
        }

        foreach ($defined as $key => $meta) {
            if (! isset($modules[$key])) {
                $modules[$key] = $meta;
            }
        }

        return $modules;
    }

    private static function definedModules(): array
    {
        return [
            'vendors' => [
                'label' => 'Users',
                'actions' => ['view', 'search', 'create', 'edit', 'approve', 'reject', 'block', 'unblock', 'export'],
            ],
            'transactions' => [
                'label' => 'Transactions',
                'actions' => ['view', 'search', 'export', 'approve', 'refund', 'reverse'],
            ],
            'commission' => [
                'label' => 'Commission',
                'actions' => ['view', 'search', 'export', 'edit', 'approve', 'settlement', 'change_commission'],
            ],
            'wallet-requests' => [
                'label' => 'Wallet',
                'actions' => ['view', 'search', 'approve', 'reject', 'settlement', 'change_limits'],
            ],
            'reports' => [
                'label' => 'Reports',
                'actions' => ['view', 'export'],
            ],
            'users' => [
                'label' => 'Administration',
                'actions' => ['view', 'create', 'edit'],
            ],
            'audit-logs' => [
                'label' => 'Audit Logs',
                'actions' => ['view', 'search'],
            ],
            'dashboard' => [
                'label' => 'Dashboard',
                'actions' => ['view'],
            ],
            'banks' => [
                'label' => 'Banks',
                'actions' => ['view', 'create', 'edit'],
            ],
            'settlements' => [
                'label' => 'Settlements',
                'actions' => ['view', 'approve', 'settlement'],
            ],
            'api-logs' => [
                'label' => 'API Logs',
                'actions' => ['view', 'export'],
            ],
        ];
    }

    public static function roles(): array
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('admin_roles')) {
                $roles = \App\Models\AdminRole::query()->orderBy('name')->pluck('name', 'slug')->all();
                if ($roles !== []) {
                    return $roles;
                }
            }
        } catch (\Throwable) {
            // Unit tests and early boot keep the built-in list.
        }

        return self::builtinRoles();
    }

    public static function builtinRoles(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'operations_admin' => 'Operations Admin',
            'finance_admin' => 'Finance Admin',
            'kyc_admin' => 'KYC Admin',
            'support_admin' => 'Support Admin',
            'report_admin' => 'Report Admin',
            'staff' => 'Custom',
        ];
    }

    public static function actionsFor(string $module): array
    {
        return self::modules()[$module]['actions'] ?? [];
    }

    public static function token(string $module, string $action): string
    {
        return $module.':'.$action;
    }

    public static function allows(string $module, string $action): bool
    {
        $admin = Auth::guard('admin')->user();

        return $admin !== null && $admin->hasPermission($module, $action);
    }

    public static function preset(string $role): array
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('admin_roles')) {
                $stored = \App\Models\AdminRole::query()->where('slug', $role)->value('permissions');
                if (is_array($stored)) {
                    return array_values(array_filter(
                        $stored,
                        fn ($token) => is_string($token) && self::isValidToken($token)
                    ));
                }
            }
        } catch (\Throwable) {
            // Fall through to the built-in matrix.
        }

        return self::builtinPreset($role);
    }

    public static function builtinPreset(string $role): array
    {
        $levels = match ($role) {
            'operations_admin' => [
                'vendors' => 'view_edit',
                'transactions' => 'full',
                'commission' => 'view',
                'wallet-requests' => 'view',
                'reports' => 'full',
            ],
            'finance_admin' => [
                'vendors' => 'view',
                'transactions' => 'view',
                'commission' => 'full',
                'wallet-requests' => 'full',
                'reports' => 'full',
            ],
            'kyc_admin' => [
                'vendors' => 'full',
                'transactions' => 'view',
                'reports' => 'limited',
            ],
            'support_admin' => [
                'vendors' => 'view_edit',
                'transactions' => 'view',
                'reports' => 'limited',
            ],
            'report_admin' => [
                'vendors' => 'view',
                'transactions' => 'view',
                'commission' => 'view',
                'wallet-requests' => 'view',
                'reports' => 'full',
            ],
            default => [],
        };

        $tokens = [self::token('dashboard', 'view')];
        foreach ($levels as $module => $level) {
            foreach (self::levelActions($module, $level) as $action) {
                $tokens[] = self::token($module, $action);
            }
        }

        return array_values(array_unique($tokens));
    }

    public static function levelActions(string $module, string $level): array
    {
        $actions = self::actionsFor($module);

        $allowed = match ($level) {
            'full' => $actions,
            'view_edit' => ['view', 'search', 'create', 'edit'],
            'view' => ['view', 'search'],
            'limited' => ['view'],
            default => [],
        };

        return array_values(array_intersect($actions, $allowed));
    }

    public static function isValidToken(string $token): bool
    {
        [$module, $action] = array_pad(explode(':', $token, 2), 2, '');

        return in_array($action, self::actionsFor($module), true);
    }
}
