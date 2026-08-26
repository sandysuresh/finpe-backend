<?php

namespace App\Models;

use App\Support\AdminModules;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
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

    public function allowedModules(): array
    {
        if ($this->isSuperAdmin()) {
            return AdminModules::keys();
        }

        $this->loadMissing('modulePermissions');

        return $this->modulePermissions->pluck('module')->all();
    }

    public function syncModules(array $modules): void
    {
        $valid = array_values(array_intersect($modules, AdminModules::keys()));

        $this->modulePermissions()->delete();

        foreach ($valid as $module) {
            $this->modulePermissions()->create(['module' => $module]);
        }

        $this->unsetRelation('modulePermissions');
    }

    public function roleLabel(): string
    {
        return $this->isSuperAdmin() ? 'Super Admin' : 'Staff';
    }
}
