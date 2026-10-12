<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Vendor Bank Accounts</h1>
        <p class="mt-1 text-sm text-slate-500">Review accounts vendors will use for Add Money.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" class="fi-input w-60 text-sm" placeholder="Search vendor, holder, or bank">
            <select wire:model.live="filterStatus" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <div class="fi-card overflow-hidden">
        @if($accounts->isEmpty())
            <div class="py-16 text-center text-sm text-slate-500">No bank accounts found.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Vendor','Holder','Account','IFSC','Bank','Status','Reason','Action'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($accounts as $account)
                            <tr>
                                <td class="px-5 py-4 text-sm">
                                    <div class="font-semibold text-slate-800">{{ $account->vendor->business_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $account->vendor->vendor_code }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $account->account_holder }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $account->account_number }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $account->ifsc_code }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $account->bank_name }}</td>
                                <td class="px-5 py-4 text-xs font-semibold">{{ ucfirst($account->status) }}</td>
                                <td class="max-w-[200px] px-5 py-4 text-xs text-red-600">{{ $account->rejection_reason ?: '—' }}</td>
                                <td class="px-5 py-4">
                                    @if($account->status === 'pending')
                                        <div class="flex gap-2">
                                            @if(auth('admin')->user()->hasPermission('wallet-requests', 'approve'))
                                                <button wire:click="openAction({{ $account->id }}, 'approve')" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Approve</button>
                                            @endif
                                            @if(auth('admin')->user()->hasPermission('wallet-requests', 'reject'))
                                                <button wire:click="openAction({{ $account->id }}, 'reject')" class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white">Reject</button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">{{ $account->reviewed_at?->format('d M Y, h:i A') ?? '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $accounts->links() }}</div>
        @endif
    </div>

    @if($showModal && $actionId)
        @php $account = $accounts->firstWhere('id', $actionId) ?? \App\Models\VendorBankAccount::with('vendor')->find($actionId); @endphp
        @if($account)
            <div class="fi-modal-overlay">
                <div class="fi-modal">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-base font-semibold text-slate-900">{{ $actionType === 'approve' ? 'Approve bank account' : 'Reject bank account' }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $account->vendor->business_name }} · {{ $account->bank_name }} · {{ $account->account_number }} · {{ $account->ifsc_code }}</p>
                    </div>
                    <div class="p-6">
                        @if($actionType === 'reject')
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rejection reason</label>
                            <textarea wire:model="rejectionReason" rows="3" class="fi-input text-sm"></textarea>
                            @error('rejectionReason')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        @else
                            <p class="text-sm text-slate-600">This account will be available in that vendor's Add Money form.</p>
                        @endif
                    </div>
                    <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button wire:click="$set('showModal', false)" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold">Cancel</button>
                        <button wire:click="confirm" class="flex-1 rounded-xl py-2.5 text-sm font-semibold text-white {{ $actionType === 'approve' ? 'bg-emerald-600' : 'bg-red-500' }}">Confirm</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
