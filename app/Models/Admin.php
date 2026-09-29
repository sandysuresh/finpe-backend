<?php

namespace App\Models;

use App\Support\AdminAccess;
use App\Support\AdminModules;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'mobile', 'department', 'branch_region',
        'password', 'role', 'status', 'two_factor_enabled',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function modulePermissions(): HasMany
    {
        return $this->hasMany(AdminModulePermission::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasModule(string $module): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->loadMissing('modulePermissions');

        return $this->modulePermissions->contains('module', $module);
    }

    public function hasPermission(string $module, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->loadMissing('modulePermissions');

        return $this->modulePermissions->contains(
            fn (AdminModulePermission $row) => $row->module === $module && $row->action === $action
        );
    }

    public function allowedModules(): array
    {
        if ($this->isSuperAdmin()) {
            return AdminModules::keys();
        }

        $this->loadMissing('modulePermissions');

        return $this->modulePermissions->pluck('module')->unique()->values()->all();
    }

    public function syncModules(array $modules): void
    {
        $valid = array_values(array_intersect($modules, AdminModules::keys()));

        $this->modulePermissions()->delete();

        foreach ($valid as $module) {
            foreach (AdminAccess::actionsFor($module) as $action) {
                $this->modulePermissions()->create([
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }

        $this->unsetRelation('modulePermissions');
    }

    public function permissionTokens(): array
    {
        if ($this->isSuperAdmin()) {
            $tokens = [];
            foreach (AdminAccess::modules() as $module => $meta) {
                foreach ($meta['actions'] as $action) {
                    $tokens[] = AdminAccess::token($module, $action);
                }
            }

            return $tokens;
        }

        $this->loadMissing('modulePermissions');

        return $this->modulePermissions
            ->map(fn (AdminModulePermission $row) => AdminAccess::token($row->module, (string) $row->action))
            ->all();
    }

    public function syncPermissions(array $tokens): void
    {
        $valid = array_values(array_unique(array_filter($tokens, fn ($token) => is_string($token) && AdminAccess::isValidToken($token))));

        $this->modulePermissions()->delete();

        foreach ($valid as $token) {
            [$module, $action] = explode(':', $token, 2);
            $this->modulePermissions()->create([
                'module' => $module,
                'action' => $action,
            ]);
        }

        $this->unsetRelation('modulePermissions');
    }

    public function roleLabel(): string
    {
        return AdminAccess::roles()[$this->role] ?? ucfirst(str_replace('_', ' ', (string) $this->role));
    }
}
