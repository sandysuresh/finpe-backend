<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Admin Users</h1>
            <p class="mt-1 text-sm text-slate-500">Create staff accounts and assign module access.</p>
        </div>
        <button type="button" wire:click="openCreate" class="fi-btn fi-btn-primary">
            <span class="text-lg leading-none">+</span>
            Add User
        </button>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" class="fi-input w-64 text-sm" placeholder="Search name or email...">
            <select wire:model.live="filterStatus" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['User','Role','Modules','Status','Action'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $user->isSuperAdmin() ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $user->roleLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($user->isSuperAdmin())
                                    <span class="text-xs font-medium text-slate-600">All modules</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($user->allowedModules() as $mod)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                                                {{ $moduleCatalog[$mod]['label'] ?? $mod }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-500">No modules</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="openEdit({{ $user->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Edit
                                    </button>
                                    @if($user->id !== auth('admin')->id())
                                        <button type="button" wire:click="toggleStatus({{ $user->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            {{ $user->status === 'active' ? 'Disable' : 'Enable' }}
                                        </button>
                                        <button type="button" wire:click="deleteUser({{ $user->id }})" wire:confirm="Delete this user?" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-sm text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $users->links() }}</div>
    </div>

    @if($showModal)
        <div class="fi-modal-overlay">
            <div class="fi-modal fi-modal-lg">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $editingId ? 'Edit User' : 'Add User' }}</h2>
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>

                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" class="fi-input">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="email" class="fi-input">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Password {{ $editingId ? '' : '*' }}</label>
                            <input type="password" wire:model="password" class="fi-input" placeholder="{{ $editingId ? 'Leave blank to keep current' : 'Min 8 characters' }}">
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                            <select wire:model="status" class="fi-input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Role</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="radio" wire:model.live="role" value="staff" class="text-blue-700">
                                Staff (selected modules only)
                            </label>
                            @if($canAssignSuper)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="radio" wire:model.live="role" value="super_admin" class="text-blue-700">
                                    Super Admin (all modules)
                                </label>
                            @endif
                        </div>
                        @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    @if($role === 'staff')
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Module permissions <span class="text-red-500">*</span></label>
                            <p class="mb-3 text-xs text-slate-500">User will only see these modules in the panel.</p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($moduleCatalog as $key => $mod)
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                                        <input type="checkbox" wire:model="modules" value="{{ $key }}" class="rounded border-slate-300 text-blue-700">
                                        {{ $mod['label'] }}
                                    </label>
                                @endforeach
                            </div>
                            @error('modules')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            Super admin has access to every module, including user management.
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" wire:click="$set('showModal', false)" class="fi-btn fi-btn-secondary">Cancel</button>
                    <button type="button" wire:click="save" class="fi-btn fi-btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create User' }}</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
