<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Admin Users</h1>
            <p class="mt-1 text-sm text-slate-500">Administration → Admin Users. Assign a role. The user gets that role’s permissions.</p>
        </div>
        @if($canCreateUsers)
        <button type="button" wire:click="openCreate" class="fi-btn fi-btn-primary">
            <span class="text-lg leading-none">+</span>
            Add New Admin
        </button>
        @endif
    </div>

    @if(session('success'))
        <div wire:key="admin-user-flash" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <input type="text" tabindex="-1" autocomplete="username" style="position:absolute; left:-9999px; width:1px; height:1px;" aria-hidden="true">
            <input wire:key="admin-user-search" wire:model.live.debounce.300ms="search" type="text" name="admin_user_filter" autocomplete="off" readonly onfocus="this.removeAttribute('readonly')" class="fi-input w-64 text-sm" placeholder="Search name, email, or mobile...">
            <select wire:model.live="filterStatus" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="fi-card overflow-hidden" wire:key="admin-user-list">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['User','Role','Modules','Status','Action'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody wire:key="admin-user-rows-{{ $users->pluck('id')->implode('-') }}" class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr wire:key="admin-user-{{ $user->id }}" class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                @if($user->mobile)
                                    <p class="text-xs text-slate-500">{{ $user->mobile }}</p>
                                @endif
                                @if($user->department || $user->branch_region)
                                    <p class="text-xs text-slate-400">{{ $user->department }}{{ $user->department && $user->branch_region ? ' · ' : '' }}{{ $user->branch_region }}</p>
                                @endif
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
                                                {{ $permissionCatalog[$mod]['label'] ?? ($moduleCatalog[$mod]['label'] ?? $mod) }}
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
                                    @if($canEditUsers)
                                    <button type="button" wire:click="openEdit({{ $user->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Edit
                                    </button>
                                    @endif
                                    @if($canEditUsers && $user->id !== auth('admin')->id())
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

    <div class="fi-modal-overlay" wire:key="admin-user-modal" @if(! $showModal) style="display:none" @endif>
            <div class="fi-modal fi-modal-admin" style="width:min(980px, calc(100vw - 32px)); max-width:980px; max-height:min(92vh, 920px); overflow:hidden; display:flex; flex-direction:column; border-radius:16px;">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $editingId ? 'Edit Admin' : 'Add New Admin' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose a role, then adjust the actions this admin is allowed to use.</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>

                <div class="space-y-6 overflow-y-auto px-6 py-5" style="flex:1 1 auto;">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Admin Name <span class="text-red-500">*</span></label>
                            <input wire:key="admin-form-name" type="text" wire:model="name" autocomplete="off" class="fi-input">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="email" class="fi-input">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="mobile" class="fi-input">
                            @error('mobile')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Department</label>
                            <input type="text" wire:model="department" class="fi-input">
                            @error('department')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Branch / Region</label>
                            <input type="text" wire:model="branchRegion" class="fi-input">
                            @error('branchRegion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Password {{ $editingId ? '' : '*' }}</label>
                            <input type="password" wire:model="password" class="fi-input" placeholder="{{ $editingId ? 'Leave blank to keep current' : 'Min 9 characters' }}">
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
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">2FA</label>
                            <select wire:model="twoFactorEnabled" class="fi-input">
                                <option value="0">Disabled</option>
                                <option value="1">Enabled</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Role</label>
                        <select wire:model="role" class="fi-input">
                            @foreach($roleOptions as $value => $label)
                                @if($value !== 'super_admin' || $canAssignSuper)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <p class="text-xs text-slate-500">Permissions come from the selected role. Change them under Administration → Permissions.</p>
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
</div>
