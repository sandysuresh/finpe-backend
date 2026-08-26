<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
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
    public string $password = '';
    public string $role = 'staff';
    public string $status = 'active';
    public array $modules = [];

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
        $this->modules = ['dashboard'];
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
        $this->password = '';
        $this->role = $user->role === 'super_admin' ? 'super_admin' : 'staff';
        $this->status = $user->status;
        $this->modules = $user->isSuperAdmin() ? AdminModules::keys() : $user->allowedModules();
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

        $rules = [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('admins', 'email')->ignore($this->editingId),
            ],
            'role' => 'required|in:super_admin,staff',
            'status' => 'required|in:active,inactive',
            'modules' => 'array',
            'modules.*' => Rule::in(AdminModules::keys()),
        ];

        if ($this->editingId === null) {
            $rules['password'] = 'required|string|min:12|max:100';
        } else {
            $rules['password'] = 'nullable|string|min:12|max:100';
        }

        if ($this->role === 'staff') {
            $rules['modules'] = 'required|array|min:1';
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
                'role' => $this->role,
                'status' => $this->status,
            ];

            if ($this->password !== '') {
                $payload['password'] = $this->password;
            }

            $user->update($payload);
        } else {
            $user = Admin::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'role' => $this->role,
                'status' => $this->status,
            ]);
        }

        if ($this->role === 'super_admin') {
            $user->modulePermissions()->delete();
        } else {
            $user->syncModules($this->modules);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $isUpdate ? 'User updated.' : 'User created.');
    }

    public function toggleStatus(int $id): void
    {
        $actor = Auth::guard('admin')->user();
        $user = Admin::findOrFail($id);

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return;
        }

        if ($user->id === $actor->id) {
            return;
        }

        if ($user->isSuperAdmin() && $user->status === 'active' && $this->isLastSuperAdmin($user->id)) {
            return;
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);
    }

    public function deleteUser(int $id): void
    {
        $actor = Auth::guard('admin')->user();
        $user = Admin::findOrFail($id);

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return;
        }

        if ($user->id === $actor->id) {
            return;
        }

        if ($user->isSuperAdmin() && $this->isLastSuperAdmin($user->id)) {
            return;
        }

        $user->delete();
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
        $this->reset(['editingId', 'name', 'email', 'password', 'formMessage']);
        $this->role = 'staff';
        $this->status = 'active';
        $this->modules = ['dashboard'];
        $this->resetValidation();
    }

    public function render()
    {
        $users = Admin::query()
            ->with('modulePermissions')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.users', [
            'users' => $users,
            'moduleCatalog' => AdminModules::all(),
            'canAssignSuper' => Auth::guard('admin')->user()->isSuperAdmin(),
        ])->layout('layouts.admin', ['title' => 'Users']);
    }
}
