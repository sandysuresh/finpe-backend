<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Support\AdminAccess;
use App\Support\AdminAudit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RolePermissions extends Component
{
    public string $role = '';

    public array $permissions = [];

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('users')) {
            abort(403);
        }

        $this->role = (string) AdminRole::query()->where('slug', '!=', 'super_admin')->orderBy('name')->value('slug');
        $this->loadPermissions();
    }

    public function updatedRole(): void
    {
        $this->loadPermissions();
    }

    public function save(): void
    {
        if (! Auth::guard('admin')->user()?->isSuperAdmin() && ! Auth::guard('admin')->user()?->hasPermission('users', 'edit')) {
            abort(403);
        }

        $role = AdminRole::query()->where('slug', $this->role)->firstOrFail();
        if ($role->slug === 'super_admin') {
            return;
        }

        $tokens = array_values(array_unique(array_filter(
            $this->permissions,
            fn ($token) => is_string($token) && AdminAccess::isValidToken($token)
        )));

        $before = count($role->permissions ?? []).' actions';
        $role->update(['permissions' => $tokens]);

        Admin::query()->where('role', $role->slug)->each(function (Admin $admin) use ($tokens) {
            $admin->syncPermissions($tokens);
        });

        AdminAudit::record(
            'Role permissions updated',
            'Success',
            $role->name,
            $before,
            count($tokens).' actions',
        );
        session()->flash('success', 'Permissions saved for '.$role->name.'. Admins with this role now use these actions.');
    }

    public function render()
    {
        return view('livewire.admin.role-permissions', [
            'roles' => AdminRole::query()->orderBy('name')->get(),
            'catalog' => AdminAccess::modules(),
            'selected' => AdminRole::query()->where('slug', $this->role)->first(),
        ])->layout('layouts.admin', ['title' => 'Permissions']);
    }

    private function loadPermissions(): void
    {
        $role = AdminRole::query()->where('slug', $this->role)->first();
        $stored = $role?->permissions;
        $this->permissions = is_array($stored) ? $stored : AdminAccess::preset($this->role);
    }
}
