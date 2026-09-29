<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Permissions</h1>
        <p class="mt-1 text-sm text-slate-500">Administration → Permissions. Choose a role, then set the actions it receives.</p>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="fi-card mb-5 px-5 py-4">
        <label class="mb-2 block text-sm font-medium text-slate-700">Role</label>
        <select wire:model.live="role" class="fi-input max-w-sm">
            @foreach($roles as $item)
                <option value="{{ $item->slug }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </div>

    @if($selected && $selected->slug === 'super_admin')
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            Super Admin always has every module and action. This role does not use a permission list.
        </div>
    @else
        <div class="fi-card p-5">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                @foreach($catalog as $module => $meta)
                    <div wire:key="role-perm-{{ $module }}" class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                        <p class="mb-2 text-sm font-semibold text-slate-900">{{ $meta['label'] }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($meta['actions'] as $action)
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700">
                                    <input type="checkbox" wire:model="permissions" value="{{ $module }}:{{ $action }}" class="rounded border-slate-300 text-blue-700">
                                    {{ \App\Support\AdminAccess::ACTIONS[$action] ?? $action }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-5 flex justify-end">
                <button type="button" wire:click="save" class="fi-btn fi-btn-primary">Save Permissions</button>
            </div>
        </div>
    @endif
</div>
