<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Support\AdminAccess;
use App\Support\AdminAudit;
use App\Support\AdminModules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $department = '';
    public string $branchRegion = '';
    public string $password = '';
    public string $role = 'operations_admin';
    public string $status = 'active';
    public bool $twoFactorEnabled = false;

    public string $formMessage = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('users')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->role = 'operations_admin';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = Admin::with('modulePermissions')->findOrFail($id);
        $actor = Auth::guard('admin')->user();
        if ($user->isSuperAdmin() && ! $actor?->isSuperAdmin()) {
            abort(403);
        }

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->mobile = (string) $user->mobile;
        $this->department = (string) $user->department;
        $this->branchRegion = (string) $user->branch_region;
        $this->password = '';
        $this->role = array_key_exists($user->role, AdminAccess::roles()) ? $user->role : 'staff';
        $this->status = $user->status;
        $this->twoFactorEnabled = (bool) $user->two_factor_enabled;
        $this->formMessage = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $actor = Auth::guard('admin')->user();

        if ($this->role === 'super_admin' && ! $actor->isSuperAdmin()) {
            $this->addError('role', 'Only a super admin can assign the super admin role.');

            return;
        }

        if ($this->editingId === null && ! $actor->hasPermission('users', 'create')) {
            abort(403);
        }
        if ($this->editingId !== null && ! $actor->hasPermission('users', 'edit')) {
            abort(403);
        }

        $rules = [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('admins', 'email')->ignore($this->editingId),
            ],
            'mobile' => 'required|string|max:20',
            'department' => 'nullable|string|max:80',
            'branchRegion' => 'nullable|string|max:80',
            'role' => ['required', Rule::in(array_keys(AdminAccess::roles()))],
            'status' => 'required|in:active,inactive',
            'twoFactorEnabled' => 'boolean',
        ];

        if ($this->editingId === null) {
            $rules['password'] = 'required|string|min:9|max:100';
        } else {
            $rules['password'] = 'nullable|string|min:9|max:100';
        }

        $this->validate($rules);

        $isUpdate = $this->editingId !== null;

        if ($this->editingId) {
            $user = Admin::findOrFail($this->editingId);

            if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
                abort(403);
            }

            if ($user->isSuperAdmin() && $this->role !== 'super_admin' && $this->isLastSuperAdmin($user->id)) {
                $this->addError('role', 'At least one super admin is required.');

                return;
            }

            if ($user->id === $actor->id && $this->status === 'inactive') {
                $this->addError('status', 'You cannot deactivate your own account.');

                return;
            }

            $payload = [
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'department' => $this->department !== '' ? $this->department : null,
                'branch_region' => $this->branchRegion !== '' ? $this->branchRegion : null,
                'role' => $this->role,
                'status' => $this->status,
                'two_factor_enabled' => $this->twoFactorEnabled,
            ];

            if ($this->password !== '') {
                $payload['password'] = $this->password;
            }

            $before = AdminAudit::snapshot($user);
            $user->update($payload);
            $user->refresh();
            $after = AdminAudit::snapshot($user).($this->password !== '' ? ' | Password: changed' : '');
        } else {
            $user = Admin::create([
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'department' => $this->department !== '' ? $this->department : null,
                'branch_region' => $this->branchRegion !== '' ? $this->branchRegion : null,
                'password' => $this->password,
                'role' => $this->role,
                'status' => $this->status,
                'two_factor_enabled' => $this->twoFactorEnabled,
            ]);
        }

        if ($this->role === 'super_admin') {
            $user->modulePermissions()->delete();
        } else {
            $user->syncPermissions(AdminAccess::preset($this->role));
        }

        AdminAudit::record(
            $isUpdate ? 'Admin user updated' : 'Admin user created',
            'Success',
            'Admin #'.$user->id.' '.$user->email,
            $isUpdate ? ($before ?? null) : null,
            $isUpdate ? ($after ?? AdminAudit::snapshot($user)) : AdminAudit::snapshot($user),
        );

        $this->search = '';
        $this->filterStatus = '';
        $this->resetPage();
        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $isUpdate ? 'User updated.' : 'User created.');
    }

    public function toggleStatus(int $id): void
    {
        $actor = Auth::guard('admin')->user();
        $user = Admin::findOrFail($id);

        if (! $actor->hasPermission('users', 'edit')) {
            return;
        }

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return;
        }

        if ($user->id === $actor->id) {
            return;
        }

        if ($user->isSuperAdmin() && $user->status === 'active' && $this->isLastSuperAdmin($user->id)) {
            return;
        }

        $before = ucfirst((string) $user->status);
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);
        AdminAudit::record(
            'Admin user status changed',
            'Success',
            'Admin #'.$user->id.' '.$user->email,
            $before,
            ucfirst((string) $user->status),
        );
    }

    public function deleteUser(int $id): void
    {
        $actor = Auth::guard('admin')->user();
        $user = Admin::findOrFail($id);

        if (! $actor->hasPermission('users', 'edit')) {
            return;
        }

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return;
        }

        if ($user->id === $actor->id) {
            return;
        }

        if ($user->isSuperAdmin() && $this->isLastSuperAdmin($user->id)) {
            return;
        }

        $label = 'Admin #'.$user->id.' '.$user->email;
        $before = AdminAudit::snapshot($user);
        $user->delete();
        AdminAudit::record('Admin user deleted', 'Success', $label, $before, 'Deleted');
    }

    private function isLastSuperAdmin(int $exceptId): bool
    {
        return Admin::query()
            ->where('role', 'super_admin')
            ->where('status', 'active')
            ->where('id', '!=', $exceptId)
            ->doesntExist();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'mobile', 'department', 'branchRegion', 'password', 'formMessage']);
        $this->role = 'operations_admin';
        $this->status = 'active';
        $this->twoFactorEnabled = false;
        $this->resetValidation();
    }

    public function render()
    {
        $users = Admin::query()
            ->with('modulePermissions')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('mobile', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('id')
            ->paginate(12);

        return view('livewire.admin.users', [
            'users' => $users,
            'moduleCatalog' => AdminModules::all(),
            'permissionCatalog' => AdminAccess::modules(),
            'roleOptions' => AdminAccess::roles(),
            'canAssignSuper' => Auth::guard('admin')->user()->isSuperAdmin(),
            'canEditUsers' => Auth::guard('admin')->user()->hasPermission('users', 'edit'),
            'canCreateUsers' => Auth::guard('admin')->user()->hasPermission('users', 'create'),
        ])->layout('layouts.admin', ['title' => 'Users']);
    }
}
