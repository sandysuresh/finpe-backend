<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

class AdminAudit
{
    public static function record(
        string $action,
        string $status,
        ?string $subjectLabel = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?object $actor = null,
        ?string $fallbackType = null,
        ?string $fallbackName = null,
    ): void {
        [$type, $id, $name, $email] = self::actor($actor, $fallbackType, $fallbackName);

        AdminAuditLog::query()->create([
            'actor_type' => $type,
            'actor_id' => $id,
            'actor_name' => $name,
            'actor_email' => $email,
            'action' => $action,
            'subject_label' => $subjectLabel,
            'old_value' => self::clip($oldValue),
            'new_value' => self::clip($newValue),
            'ip_address' => request()->ip(),
            'status' => $status,
        ]);
    }

    public static function snapshot(Admin $admin): string
    {
        return implode(' | ', [
            'Name: '.$admin->name,
            'Email: '.$admin->email,
            'Mobile: '.($admin->mobile ?: '—'),
            'Department: '.($admin->department ?: '—'),
            'Branch: '.($admin->branch_region ?: '—'),
            'Role: '.$admin->roleLabel(),
            'Status: '.ucfirst((string) $admin->status),
            '2FA: '.($admin->two_factor_enabled ? 'Enabled' : 'Disabled'),
        ]);
    }

    public static function workDetail(array $data, array $updates = [], array $params = []): ?string
    {
        $parts = [];

        foreach ($params as $param) {
            $param = self::unwrap($param);
            if (self::isScalar($param) && $param !== '' && $param !== null) {
                $parts[] = (string) $param;
            }
        }

        if ($parts !== []) {
            return 'Reference: '.implode(', ', $parts);
        }

        return null;
    }

    private static function actor(?object $actor, ?string $fallbackType, ?string $fallbackName): array
    {
        if ($actor instanceof Admin) {
            return ['admin', $actor->id, $actor->name ?: 'Admin', $actor->email];
        }

        if ($actor instanceof Vendor) {
            return ['vendor', $actor->id, $actor->business_name ?: $actor->contact_name ?: 'User', $actor->email];
        }

        $admin = Auth::guard('admin')->user();
        if ($admin instanceof Admin) {
            return ['admin', $admin->id, $admin->name ?: 'Admin', $admin->email];
        }

        $vendor = Auth::guard('vendor')->user();
        if ($vendor instanceof Vendor) {
            return ['vendor', $vendor->id, $vendor->business_name ?: $vendor->contact_name ?: 'User', $vendor->email];
        }

        $fallbackEmail = is_string($fallbackName) && str_contains($fallbackName, '@') ? $fallbackName : null;

        return [$fallbackType ?: 'admin', null, $fallbackEmail ? 'Unknown' : ($fallbackName ?: 'Unknown'), $fallbackEmail];
    }

    private static function unwrap(mixed $value): mixed
    {
        if (is_array($value) && array_key_exists(0, $value) && count($value) === 2 && is_array($value[1] ?? null)) {
            return $value[0];
        }

        return $value;
    }

    private static function isScalar(mixed $value): bool
    {
        return is_bool($value) || is_int($value) || is_float($value) || (is_string($value) && strlen($value) <= 300);
    }

    private static function skipKey(string $key): bool
    {
        return in_array($key, ['password', 'password_confirmation', 'current_password', 'new_password'], true);
    }

    private static function redact(string $key, mixed $value): mixed
    {
        $needle = strtolower($key);
        foreach (['password', 'secret', 'token', 'aadhaar', 'pan', 'pid', 'otp', 'pin', 'cvv', 'salt', 'account'] as $blocked) {
            if (str_contains($needle, $blocked)) {
                return '[redacted]';
            }
        }

        return $value;
    }

    private static function redactTree(mixed $value): mixed
    {
        if (! is_array($value)) {
            return is_string($value) && strlen($value) > 300 ? substr($value, 0, 300) : $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = is_string($key) && self::skipKey($key) ? '[redacted]' : self::redactTree($item);
        }

        return $out;
    }

    private static function clip(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strlen($value) > 4000 ? substr($value, 0, 4000).'…' : $value;
    }
}
