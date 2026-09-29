<?php

namespace App\Http\Middleware;

use App\Support\AdminAudit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogPanelActivity
{
    private const SKIP_METHODS = [
        'render', 'openCreate', 'openEdit', 'openModal', 'openAction', 'openAssign', 'openApis',
        'openCreateEndpoint', 'openEditEndpoint', 'setTab', 'goToStep', 'nextStep', 'previousStep',
        'close', 'toggle', 'fillBeneficiary', 'preview', 'newTransaction', 'resetFilters',
        'thisMonth', 'previousMonth', 'yesterday', 'addRequestParam', 'removeRequestParam',
        'addResponseParam', 'removeResponseParam', 'updatedRole', 'updatedOnDate',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->is('livewire/update') || ! $request->isMethod('POST')) {
            return;
        }

        try {
            $this->logCalls($request, $response);
        } catch (Throwable) {
            // A log failure must not change the panel response.
        }
    }

    private function logCalls(Request $request, Response $response): void
    {
        $status = $response->isSuccessful() || $response->isRedirection() ? 'Success' : 'Failed';

        foreach ($request->input('components', []) as $component) {
            if (! is_array($component)) {
                continue;
            }

            $snapshot = json_decode((string) ($component['snapshot'] ?? ''), true);
            $name = is_array($snapshot) ? (string) ($snapshot['memo']['name'] ?? '') : '';
            if ($name === '' || str_contains($name, 'audit-logs') || str_contains($name, 'notification-bell')) {
                continue;
            }

            $data = is_array($snapshot['data'] ?? null) ? $snapshot['data'] : [];
            $updates = is_array($component['updates'] ?? null) ? $component['updates'] : [];

            foreach ($component['calls'] ?? [] as $call) {
                if (! is_array($call)) {
                    continue;
                }

                $method = (string) ($call['method'] ?? '');
                if ($method === '' || str_starts_with($method, '$') || str_starts_with($method, 'updating') || in_array($method, self::SKIP_METHODS, true)) {
                    continue;
                }

                if ($this->loggedExplicitly($name, $method)) {
                    continue;
                }

                $params = is_array($call['params'] ?? null) ? $call['params'] : [];
                AdminAudit::record(
                    $this->actionLabel($name, $method),
                    $status,
                    $this->subject($name, $data),
                    null,
                    AdminAudit::workDetail($data, $updates, $params),
                );
            }
        }
    }

    private function loggedExplicitly(string $component, string $method): bool
    {
        return match ($component) {
            'admin.users' => in_array($method, ['save', 'toggleStatus', 'deleteUser'], true),
            'admin.vendors.show' => in_array($method, ['approveKyc', 'rejectKyc'], true),
            'admin.wallet-requests' => $method === 'confirm',
            'admin.banks' => $method === 'toggleActive',
            'admin.roles' => in_array($method, ['save', 'deleteRole'], true),
            'admin.role-permissions' => $method === 'save',
            default => false,
        };
    }

    private function actionLabel(string $component, string $method): string
    {
        $who = str_starts_with($component, 'vendor.') ? 'User' : 'Admin';
        $page = str($component)->after('.')->replace(['.', '-'], ' ')->title()->toString();

        return $who.' '.$page.' — '.$method;
    }

    private function subject(string $component, array $data): ?string
    {
        foreach (['email', 'name', 'vendorId', 'editingId', 'reference', 'amount'] as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && $value !== '') {
                return $key.': '.$value;
            }
            if (is_int($value) && $value > 0) {
                return $key.': '.$value;
            }
        }

        return $component;
    }
}
