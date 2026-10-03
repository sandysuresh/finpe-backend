<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Commission Rules</h1>
            <p class="mt-1 text-sm text-slate-500">A rule is chosen by vendor, provider, and type. An AePS merchant rule outranks the vendor rule.</p>
        </div>
        @if($canChange)
            <button type="button" wire:click="openCreate" class="fi-btn fi-btn-primary">Add rule</button>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-6">
        <input wire:model.live.debounce.300ms="search" type="text" class="fi-input text-sm" placeholder="Search name">
        <select wire:model.live="filterVendor" class="fi-input text-sm"><option value="">Vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>@endforeach</select>
        <select wire:model.live="filterMerchant" class="fi-input text-sm"><option value="">Merchant</option>@foreach($filterMerchants as $merchant)<option value="{{ $merchant->id }}">{{ $merchant->code }}</option>@endforeach</select>
        <select wire:model.live="filterProvider" class="fi-input text-sm"><option value="">Provider</option>@foreach($providers as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select>
        <select wire:model.live="filterType" class="fi-input text-sm"><option value="">Type</option><option value="payout">Payout</option><option value="aeps">AEPS</option></select>
        <select wire:model.live="filterStatus" class="fi-input text-sm"><option value="">Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
    </div>

    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead><tr>@foreach(['Name','Vendor','Provider','Type','Merchant','Commission type','Value','Priority','Status',''] as $col)<th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rules as $rule)
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold">{{ $rule->name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->vendor->business_name ?? 'Any vendor' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $providers[$rule->provider] ?? ($rule->provider ?: '—') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->type === 'aeps' ? 'AEPS' : 'Payout' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->type === 'aeps' ? ($rule->merchant->code ?? '—') : '—' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->calc_type }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->value }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->priority ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $rule->status }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            @if($canChange)
                                <button type="button" wire:click="openEdit({{ $rule->id }})" class="text-blue-700">Edit</button>
                                <button type="button" wire:click="toggle({{ $rule->id }})" class="ml-3 text-slate-600">{{ $rule->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">No commission rules.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $rules->links() }}</div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4">
            <form wire:submit="save" class="fi-card w-full max-w-2xl p-6">
                <h2 class="text-lg font-bold">{{ $editingId ? 'Edit rule' : 'New rule' }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label class="text-sm">Name<input wire:model="name" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Vendor<select wire:model.live="vendorId" class="fi-input mt-1 w-full"><option value="">Any vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>@endforeach</select></label>
                    <label class="text-sm">Provider<select wire:model="provider" class="fi-input mt-1 w-full"><option value="">Select</option>@foreach($providers as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="text-sm">Type<select wire:model.live="type" class="fi-input mt-1 w-full"><option value="">Select</option><option value="payout">Payout</option><option value="aeps">AEPS</option></select></label>
                    @if($type === 'aeps')
                        <label class="text-sm">Merchant<select wire:model="merchantId" class="fi-input mt-1 w-full"><option value="">Any merchant</option>@foreach($merchants as $merchant)<option value="{{ $merchant->id }}">{{ $merchant->code }}</option>@endforeach</select></label>
                    @endif
                    <label class="text-sm">Commission type<select wire:model="calcType" class="fi-input mt-1 w-full"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></label>
                    <label class="text-sm">Value<input wire:model="value" type="number" step="0.01" min="0" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Priority<input wire:model="priority" type="number" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Effective from<input wire:model="effectiveFrom" type="datetime-local" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Effective to<input wire:model="effectiveTo" type="datetime-local" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Status<select wire:model="status" class="fi-input mt-1 w-full"><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
                </div>
                @if($errors->any())<p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>@endif
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)" class="fi-btn">Cancel</button>
                    <button type="submit" class="fi-btn fi-btn-primary">Save</button>
                </div>
            </form>
        </div>
    @endif
</div>
