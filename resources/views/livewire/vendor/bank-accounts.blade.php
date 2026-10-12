<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bank Accounts</h1>
            <p class="mt-1 text-sm text-slate-500">Add the account you transfer from. Admin must approve it before Add Money.</p>
        </div>
        <button wire:click="openCreate" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
            Add Bank Account
        </button>
    </div>

    @if($successMsg)
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $successMsg }}</div>
    @endif

    <div class="fi-card overflow-hidden">
        @if($accounts->isEmpty())
            <div class="py-16 text-center text-sm text-slate-500">No bank accounts yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Account holder','Account','IFSC','Bank','Status','Rejection reason','Action'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($accounts as $account)
                            @php
                                $sc = match($account->status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-red-50 text-red-600',
                                    default => 'bg-amber-50 text-amber-700',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-800">{{ $account->account_holder }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $account->maskedAccount() }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $account->ifsc_code }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $account->bank_name }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $sc }}">{{ ucfirst($account->status) }}</span></td>
                                <td class="max-w-[220px] px-5 py-4 text-xs text-red-600">{{ $account->rejection_reason ?: '—' }}</td>
                                <td class="px-5 py-4">
                                    <button wire:click="openEdit({{ $account->id }})" class="text-xs font-semibold text-violet-700">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $accounts->links() }}</div>
        @endif
    </div>

    @if($showModal)
        <div class="fi-modal-overlay">
            <div class="fi-modal">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-base font-semibold text-slate-900">{{ $editMode ? 'Edit Bank Account' : 'Add Bank Account' }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Saving sends this account back for admin verification.</p>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Account holder</label>
                        <input wire:model="accountHolder" type="text" class="fi-input text-sm">
                        @error('accountHolder')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Account number</label>
                        <input wire:model="accountNumber" type="text" class="fi-input text-sm">
                        @error('accountNumber')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">IFSC</label>
                        <input wire:model="ifscCode" type="text" class="fi-input text-sm">
                        @error('ifscCode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Bank name</label>
                        <input wire:model="bankName" type="text" class="fi-input text-sm">
                        @error('bankName')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button wire:click="$set('showModal', false)" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700">Cancel</button>
                    <button wire:click="save" class="flex-1 rounded-xl bg-violet-600 py-2.5 text-sm font-semibold text-white">Submit for verification</button>
                </div>
            </div>
        </div>
    @endif
</div>
