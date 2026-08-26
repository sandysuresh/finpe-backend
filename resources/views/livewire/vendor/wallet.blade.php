<div>

    {{-- ── HEADER ── --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wallet</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your balance, top-up requests and ledger.</p>
        </div>
        <button wire:click="openModal"
                class="flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Money Request
        </button>
    </div>

    {{-- ── SUCCESS ALERT ── --}}
    @if($successMsg)
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-emerald-800">{{ $successMsg }}</p>
    </div>
    @endif

    {{-- ── BALANCE CARDS ── --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="fi-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Available Balance</p>
                    <h2 class="mt-3 text-[32px] font-bold leading-none text-slate-900">₹{{ $availableBalance }}</h2>
                    <p class="mt-2 text-xs text-slate-400">Ready to use for transfers</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100">
                    <svg class="h-6 w-6 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <button wire:click="openModal"
                    class="mt-4 w-full rounded-lg border border-violet-200 bg-violet-50 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-100">
                + Add Money
            </button>
        </div>

        <div class="fi-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Hold Balance</p>
                    <h2 class="mt-3 text-[32px] font-bold leading-none text-amber-600">₹{{ $holdBalance }}</h2>
                    <p class="mt-2 text-xs text-slate-400">Pending clearance</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100">
                    <svg class="h-6 w-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="fi-card p-6">
            @php
                $pending = $topupRequests->where('status','pending')->count() ?? 0;
            @endphp
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Pending Requests</p>
                    <h2 class="mt-3 text-[32px] font-bold leading-none text-slate-900">
                        {{ $topupRequests->where('status','pending')->count() }}
                    </h2>
                    <p class="mt-2 text-xs text-slate-400">Awaiting admin approval</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABS ── --}}
    <div class="mb-5 flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
        <button wire:click="$set('tab','ledger')"
                class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition
                       {{ $tab === 'ledger' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Wallet Ledger
        </button>
        <button wire:click="$set('tab','requests')"
                class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition
                       {{ $tab === 'requests' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Add Money Requests
        </button>
    </div>


    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- TAB: LEDGER --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($tab === 'ledger')
    <div class="fi-card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Wallet Statement</h3>
                <p class="mt-0.5 text-xs text-slate-400">All credit and debit entries</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterType"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 outline-none focus:border-violet-400">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
                <input type="date" wire:model.live="filterFrom"
                       class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-violet-400">
                <input type="date" wire:model.live="filterTo"
                       class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-violet-400">
                <button wire:click="resetFilters"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-500 hover:bg-slate-50">
                    Reset
                </button>
            </div>
        </div>

        @if(!$ledger || $ledger->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-slate-700">No ledger entries yet</h3>
                <p class="mt-1 text-xs text-slate-400">Your wallet transactions will appear here once money is added.</p>
                <button wire:click="openModal"
                        class="mt-5 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                    Request Add Money
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Date','Description','Reference','Type','Amount','Balance After'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($ledger as $entry)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                                <td class="max-w-[200px] truncate px-5 py-4 text-xs text-slate-700">{{ $entry->description ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-500">{{ $entry->reference ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                                        {{ $entry->type === 'credit' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                        {{ ucfirst($entry->type) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-bold
                                    {{ $entry->type === 'credit' ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $entry->type === 'credit' ? '+' : '-' }}₹{{ number_format((float)$entry->amount,2) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-slate-900">
                                    ₹{{ number_format((float)$entry->balance_after,2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $ledger->links() }}</div>
        @endif
    </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- TAB: ADD MONEY REQUESTS --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($tab === 'requests')
    <div class="fi-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Add Money Requests</h3>
                <p class="mt-0.5 text-xs text-slate-400">All your wallet top-up requests and their status</p>
            </div>
            <button wire:click="openModal"
                    class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-700">
                + New Request
            </button>
        </div>

        @if($topupRequests->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-slate-700">No requests yet</h3>
                <p class="mt-1 text-xs text-slate-400">Submit your first add money request to get started.</p>
                <button wire:click="openModal"
                        class="mt-5 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                    Request Add Money
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Reference','Amount','Payment Mode','UTR / Ref','Bank','Remarks','Status','Date','Admin Note'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($topupRequests as $req)
                            @php
                                $sc = match($req->status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-red-50 text-red-600',
                                    default    => 'bg-amber-50 text-amber-700',
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-slate-800">{{ $req->reference }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900">₹{{ number_format((float)$req->amount,2) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600">{{ ucwords(str_replace('_',' ',$req->payment_mode)) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-500">{{ $req->transaction_ref ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $req->bank_name ?? '—' }}</td>
                                <td class="max-w-[150px] truncate px-5 py-4 text-xs text-slate-500">{{ $req->remarks ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $sc }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-400">{{ $req->created_at->format('d M Y') }}</td>
                                <td class="max-w-[180px] px-5 py-4 text-xs text-slate-500">
                                    @if($req->admin_note)
                                        <span class="{{ $req->status === 'rejected' ? 'text-red-600 font-medium' : '' }}">{{ $req->admin_note }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $topupRequests->links() }}</div>
        @endif
    </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- ADD MONEY MODAL --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="fi-modal-overlay">
        <div class="fi-modal">

            {{-- Modal header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Add Money Request</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Submit details of your deposit. Admin will verify and credit.</p>
                </div>
                <button wire:click="$set('showModal',false)"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal body --}}
            <div class="space-y-4 p-6">

                {{-- Amount --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Amount (₹) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">₹</span>
                        <input wire:model="amount" type="number" min="1" step="0.01"
                               class="fi-input pl-8 text-sm" placeholder="0.00">
                    </div>
                    @error('amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Payment Mode --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Payment Mode <span class="text-red-500">*</span></label>
                    <select wire:model.live="paymentMode" class="fi-input text-sm">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="neft">NEFT</option>
                        <option value="rtgs">RTGS</option>
                        <option value="imps">IMPS</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                {{-- UTR / Transaction Ref --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                        {{ in_array($paymentMode,['cheque']) ? 'Cheque Number' : 'UTR / Transaction Reference' }}
                    </label>
                    <input wire:model="transactionRef" type="text" class="fi-input text-sm"
                           placeholder="{{ in_array($paymentMode,['cheque']) ? 'Cheque number' : 'UTR number / Transaction ID' }}">
                    @error('transactionRef')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Bank Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Bank Name</label>
                    <input wire:model="bankName" type="text" class="fi-input text-sm" placeholder="e.g. SBI, HDFC">
                </div>

                {{-- Remarks --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Remarks</label>
                    <textarea wire:model="remarks" rows="2" class="fi-input text-sm"
                              placeholder="Any additional notes for admin..."></textarea>
                </div>

                {{-- Info box --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                    <p class="text-xs font-medium text-blue-700">
                        ℹ After submitting, admin will verify your payment and credit the amount to your wallet — usually within 1-4 hours.
                    </p>
                </div>
            </div>

            {{-- Modal footer --}}
            <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="$set('showModal',false)"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button wire:click="submitRequest"
                        class="flex-1 rounded-xl bg-violet-600 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                    Submit Request
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
