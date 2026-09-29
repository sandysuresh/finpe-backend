<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Support\AdminAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Roles extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('users')) {
            abort(403);
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $role = AdminRole::query()->findOrFail($id);
        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->description = (string) $role->description;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! Auth::guard('admin')->user()?->isSuperAdmin() && ! Auth::guard('admin')->user()?->hasPermission('users', 'edit') && ! Auth::guard('admin')->user()?->hasPermission('users', 'create')) {
            abort(403);
        }

        $this->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:180',
        ]);

        if ($this->editingId) {
            $role = AdminRole::query()->findOrFail($this->editingId);
            $before = $role->name;
            $role->update([
                'name' => $this->name,
                'description' => $this->description !== '' ? $this->description : null,
            ]);
            AdminAudit::record('Role updated', 'Success', $role->name, $before, $role->name);
        } else {
            $slug = $this->uniqueSlug($this->name);
            $role = AdminRole::query()->create([
                'slug' => $slug,
                'name' => $this->name,
                'description' => $this->description !== '' ? $this->description : null,
                'is_system' => false,
                'permissions' => [],
            ]);
            AdminAudit::record('Role created', 'Success', $role->name, null, $role->name);
        }

        $this->showModal = false;
        session()->flash('success', 'Role saved. Set its actions under Administration → Permissions.');
    }

    public function deleteRole(int $id): void
    {
        if (! Auth::guard('admin')->user()?->isSuperAdmin() && ! Auth::guard('admin')->user()?->hasPermission('users', 'edit')) {
            abort(403);
        }

        $role = AdminRole::query()->findOrFail($id);
        if ($role->is_system || $role->slug === 'super_admin') {
            return;
        }

        if (Admin::query()->where('role', $role->slug)->exists()) {
            session()->flash('error', 'This role is assigned to an admin, so it cannot be deleted.');

            return;
        }

        $name = $role->name;
        $role->delete();
        AdminAudit::record('Role deleted', 'Success', $name, $name, 'Deleted');
    }

    public function render()
    {
        return view('livewire.admin.roles', [
            'roles' => AdminRole::query()->orderByDesc('is_system')->orderBy('name')->get(),
            'usage' => Admin::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ])->layout('layouts.admin', ['title' => 'Roles']);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'role';
        $slug = $base;
        $n = 2;
        while (AdminRole::query()->where('slug', $slug)->exists()) {
            $slug = $base.'_'.$n;
            $n++;
        }

        return $slug;
    }
}
