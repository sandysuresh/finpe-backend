<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Payout Transactions</h1>
        <p class="mt-1 text-sm text-slate-500">Read-only payout transactions.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <select wire:model.live="status" class="fi-input text-sm" aria-label="Status">
            <option value="">Status</option>
            <option value="pending">Pending</option>
            <option value="success">Success</option>
            <option value="failed">Failed</option>
        </select>
    </div>

    <div class="fi-card overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    @foreach(['FinPe reference','Vendor','Payout Provider','Amount','Payment mode','Beneficiary bank','Account','IFSC','Provider reference','Status','Created'] as $col)
                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $txn)
                    <tr class="cursor-pointer hover:bg-slate-50" wire:click="show({{ $txn->id }})">
                        <td class="px-3 py-3 text-sm font-semibold text-blue-700">{{ $txn->reference }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->vendor->business_name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm font-semibold text-slate-900">{{ $txn->type === 'payout' ? (\App\Support\CommissionProviders::name($txn->payout_provider) ?: '—') : '—' }}</td>
                        <td class="px-3 py-3 text-sm text-slate-900">₹{{ number_format((float) $txn->amount, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ strtoupper((string) $txn->service) }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->beneficiary_bank_code ?: ($txn->bank_name ?: '—') }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $this->maskAccount($txn->account_number) }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->ifsc_code ?: '—' }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->bank_reference ?: '—' }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->status }}</td>
                        <td class="px-3 py-3 text-xs text-slate-600">{{ $txn->created_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-5 py-16 text-center text-sm text-slate-500">No payout transactions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $transactions->links() }}</div>
    </div>

    @if($detail)
        @php
            $charge = (float) $detail->payout_charge;
            $total = round((float) $detail->amount + $charge, 2);
            $bank = trim(($detail->bank_name ?: '').($detail->bank_name && $detail->beneficiary_bank_code ? ' · ' : '').($detail->beneficiary_bank_code ?: ''));
        @endphp
        <div class="fixed inset-0 flex items-center justify-center overflow-hidden bg-slate-950/55 px-3 sm:px-6" style="z-index: 100; padding-top: 4.75rem; padding-bottom: 1rem;" wire:click.self="$set('detailId', null)" wire:key="payout-detail-{{ $detail->id }}">
            <div class="flex min-h-0 w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5" style="max-height: min(90vh, calc(100vh - 4.75rem - 1rem));" role="dialog" aria-labelledby="payout-detail-title"
                x-data
                x-init="$refs.modalBody.scrollTop = 0; $nextTick(() => { $refs.modalBody.scrollTop = 0 })">
                <div class="shrink-0 bg-slate-900 px-5 py-4 text-white sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5M8 13h8M8 17h5"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h2 id="payout-detail-title" class="text-lg font-bold tracking-tight sm:text-xl">Payout Transaction Details</h2>
                                <p class="mt-0.5 text-sm text-slate-300">Complete information of the payout transaction</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold ring-1 ring-inset {{ $this->statusBadgeClass($detail->status) }}">{{ ucfirst((string) $detail->status) }}</span>
                            <button type="button" class="rounded-lg p-1.5 text-indigo-100 hover:bg-white/10" wire:click="$set('detailId', null)" aria-label="Close">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-3 border-t border-white/10 pt-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-300">FinPe reference</p>
                                <p class="mt-0.5 flex items-center gap-1 text-sm font-semibold">
                                    <span class="truncate">{{ $detail->reference }}</span>
                                    <button type="button" class="rounded p-0.5 text-slate-300 hover:bg-white/10 hover:text-white" aria-label="Copy FinPe reference" @click="navigator.clipboard.writeText(@js($detail->reference))">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                                    </button>
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-300">Vendor</p>
                                <p class="mt-0.5 truncate text-sm font-semibold">{{ $detail->vendor->business_name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-300">Transaction date</p>
                                <p class="mt-0.5 text-sm font-semibold">{{ $detail->created_at?->format('d M Y, h:i A') ?: '—' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.payout-transactions.receipt', $detail->reference) }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 21h16"/></svg>
                            Download receipt
                        </a>
                    </div>
                </div>

                <div x-ref="modalBody" class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-5 py-5 sm:px-6">
                    <div class="grid grid-cols-2 gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm lg:grid-cols-4">
                        <div class="px-2 py-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout amount</p>
                            <p class="mt-1 text-xl font-bold tracking-tight text-slate-900">₹{{ number_format((float) $detail->amount, 2) }}</p>
                        </div>
                        <div class="px-2 py-2 lg:border-l lg:border-slate-100">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Commission / charge</p>
                            <p class="mt-1 text-xl font-bold tracking-tight text-slate-900">₹{{ number_format($charge, 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-blue-700">Total debited</p>
                            <p class="mt-1 text-xl font-bold tracking-tight text-blue-900">₹{{ number_format($total, 2) }}</p>
                        </div>
                        <div class="px-2 py-2 lg:border-l lg:border-slate-100">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider status</p>
                            <p class="mt-2"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $this->statusBadgeClass($detail->status) }}">{{ ucfirst((string) $detail->status) }}</span></p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <h3 class="flex items-center gap-2 border-b border-indigo-100 bg-indigo-50 px-4 py-3 text-sm font-bold text-slate-900">
                                <svg class="h-4 w-4 text-indigo-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/></svg>
                                Beneficiary details
                            </h3>
                            <dl class="divide-y divide-slate-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                    <div class="px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Name</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->beneficiary_name ?: '—' }}</dd></div>
                                    <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0" x-data="{ open: false }">
                                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Account</dt>
                                        <dd class="mt-1 flex items-center gap-1.5 font-mono text-sm font-bold tracking-wide text-slate-900">
                                            <span x-text="open ? @js($detail->account_number ?: '—') : @js($this->maskAccount($detail->account_number))">{{ $this->maskAccount($detail->account_number) }}</span>
                                            @if($detail->account_number)
                                                <button type="button" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="open = !open" :aria-label="open ? 'Hide account' : 'Show account'">
                                                    <svg x-show="!open" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg x-show="open" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18.2 18.2 0 0 1-3.2 4.2M6.1 6.1C3.6 7.8 2 12 2 12a18.4 18.4 0 0 0 4.2 5"/></svg>
                                                </button>
                                            @endif
                                        </dd>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                    <div class="px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">IFSC</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->ifsc_code ?: '—' }}</dd></div>
                                    <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Bank</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $bank !== '' ? $bank : '—' }}</dd></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                    <div class="px-4 py-3" x-data="{ open: false }">
                                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Mobile</dt>
                                        <dd class="mt-1 flex items-center gap-1.5 font-mono text-sm font-bold tracking-wide text-slate-900">
                                            <span x-text="open ? @js($detail->beneficiary_mobile ?: '—') : @js($this->maskMobile($detail->beneficiary_mobile))">{{ $this->maskMobile($detail->beneficiary_mobile) }}</span>
                                            @if($detail->beneficiary_mobile)
                                                <button type="button" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="open = !open" :aria-label="open ? 'Hide mobile' : 'Show mobile'">
                                                    <svg x-show="!open" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg x-show="open" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18.2 18.2 0 0 1-3.2 4.2M6.1 6.1C3.6 7.8 2 12 2 12a18.4 18.4 0 0 0 4.2 5"/></svg>
                                                </button>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Location</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->beneficiary_location ?: '—' }}</dd></div>
                                </div>
                            </dl>
                        </section>

                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <h3 class="flex items-center gap-2 border-b border-indigo-100 bg-indigo-50 px-4 py-3 text-sm font-bold text-slate-900">
                                <svg class="h-4 w-4 text-indigo-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                                Transaction details
                            </h3>
                            <dl class="divide-y divide-slate-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                    <div class="px-4 py-3">
                                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">FinPe reference</dt>
                                        <dd class="mt-1 flex items-center gap-1 text-sm font-bold text-indigo-800">
                                            <span class="break-all">{{ $detail->reference }}</span>
                                            <button type="button" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Copy FinPe reference" @click="navigator.clipboard.writeText(@js($detail->reference))">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                                            </button>
                                        </dd>
                                    </div>
                                    <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0">
                                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Merchant reference</dt>
                                        <dd class="mt-1 flex items-center gap-1 text-sm font-bold text-slate-900">
                                            <span class="break-all">{{ $detail->merchant_ref ?: '—' }}</span>
                                            @if($detail->merchant_ref)
                                                <button type="button" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Copy merchant reference" @click="navigator.clipboard.writeText(@js($detail->merchant_ref))">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                                                </button>
                                            @endif
                                        </dd>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                    <div class="px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Payment mode</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ strtoupper((string) $detail->service) ?: '—' }}</dd></div>
                                    <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Payment purpose</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->payment_purpose ?: '—' }}</dd></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                    <div class="px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Created</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->created_at?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                                    <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Updated</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->updated_at?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                                </div>
                            </dl>
                        </section>
                    </div>

                    <section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <h3 class="flex items-center gap-2 border-b border-indigo-100 bg-indigo-50 px-4 py-3 text-sm font-bold text-slate-900">
                            <svg class="h-4 w-4 text-indigo-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3 4 7v6c0 5 3.4 7.6 8 8 4.6-.4 8-3 8-8V7l-8-4Z"/></svg>
                            Provider details
                        </h3>
                        <dl class="divide-y divide-slate-100">
                            <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                <div class="px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Payout Provider</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->type === 'payout' ? (\App\Support\CommissionProviders::name($detail->payout_provider) ?: '—') : '—' }}</dd></div>
                                <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0">
                                    <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Provider reference</dt>
                                    <dd class="mt-1 flex items-center gap-1 text-sm font-bold text-slate-900">
                                        <span class="break-all">{{ $detail->bank_reference ?: '—' }}</span>
                                        @if($detail->bank_reference)
                                            <button type="button" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Copy provider reference" @click="navigator.clipboard.writeText(@js($detail->bank_reference))">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                                            </button>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 sm:divide-x sm:divide-slate-100">
                                <div class="px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Provider status</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $this->statusBadgeClass($detail->status) }}">{{ ucfirst((string) $detail->status) }}</span></dd></div>
                                <div class="border-t border-slate-100 px-4 py-3 sm:border-t-0"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Provider message</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->failure_reason ?: '—' }}</dd></div>
                            </div>
                        </dl>
                    </section>
                </div>

                <div class="flex shrink-0 items-center justify-between gap-3 border-t border-slate-200 bg-white px-5 py-3.5 sm:px-6">
                    <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="$set('detailId', null)">Close</button>
                    <a href="{{ route('admin.payout-transactions.receipt', $detail->reference) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 21h16"/></svg>
                        Download receipt
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
