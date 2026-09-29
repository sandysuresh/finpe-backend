<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Roles</h1>
            <p class="mt-1 text-sm text-slate-500">Administration → Roles. Set actions under Permissions. A user receives those actions when this role is assigned.</p>
        </div>
        <button type="button" wire:click="openCreate" class="fi-btn fi-btn-primary">+ Add Role</button>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
    @endif

    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead>
                <tr>
                    @foreach(['Role','Key','Admins','Action'] as $col)
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($roles as $role)
                    <tr wire:key="role-{{ $role->id }}">
                        <td class="px-5 py-3">
                            <div class="text-sm font-semibold text-slate-900">{{ $role->name }}</div>
                            @if($role->description)
                                <div class="text-xs text-slate-500">{{ $role->description }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $role->slug }}</td>
                        <td class="px-5 py-3 text-sm text-slate-700">{{ $usage[$role->slug] ?? 0 }}</td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <button type="button" wire:click="openEdit({{ $role->id }})" class="fi-btn fi-btn-secondary">Edit</button>
                                @if(! $role->is_system)
                                    <button type="button" wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role?" class="fi-btn bg-red-600 text-white hover:bg-red-700">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-bold text-slate-900">{{ $editingId ? 'Edit Role' : 'Add Role' }}</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Role name</label>
                        <input type="text" wire:model="name" class="fi-input" placeholder="Branch Admin">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                        <input type="text" wire:model="description" class="fi-input">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)" class="fi-btn fi-btn-secondary">Cancel</button>
                    <button type="button" wire:click="save" class="fi-btn fi-btn-primary">Save Role</button>
                </div>
            </div>
        </div>
    @endif
</div>
