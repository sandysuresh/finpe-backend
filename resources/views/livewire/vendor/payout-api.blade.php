<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Payout API</h1>
        <p class="mt-1 text-sm font-semibold text-slate-900">Payout Provider: {{ $payoutProviderName }}</p>
        <p class="mt-1 text-sm text-slate-500">Your payout transactions, wallet movements, and the FinPe payout API.</p>
    </div>

    @unless($enabled)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-medium text-amber-950">
            Payout API access is not enabled.
        </div>
    @else
        @if($providers !== [])
            <div class="mb-4 flex flex-wrap gap-2" aria-label="Payout providers">
                @foreach($providers as $provider)
                    <button type="button" wire:click="selectProvider('{{ $provider['code'] }}')"
                            class="rounded-full px-4 py-2 text-sm font-semibold {{ $providerCode === $provider['code'] ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                        {{ $provider['name'] }}@if($provider['active']) <span class="ml-1 text-[11px] font-medium uppercase tracking-wide {{ $providerCode === $provider['code'] ? 'text-slate-300' : 'text-slate-400' }}">Configured</span>@endif
                    </button>
                @endforeach
            </div>
        @endif

        <div class="mb-5 flex flex-wrap gap-2">
            @foreach(['transactions' => 'Payout transactions', 'wallet' => 'Wallet history', 'docs' => 'API documentation'] as $key => $label)
                <button type="button" wire:click="setSection('{{ $key }}')"
                        class="rounded-full px-4 py-2 text-sm font-semibold {{ $section === $key ? 'bg-violet-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if($section !== 'docs')
            <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 sm:grid-cols-2 lg:grid-cols-4">
                <input wire:model.live.debounce.300ms="search" type="search" class="fi-input text-sm" placeholder="{{ $section === 'wallet' ? 'FinPe reference' : 'FinPe, merchant, or provider reference' }}" aria-label="Search">
                @if($section === 'transactions')
                    <select wire:model.live="status" class="fi-input text-sm" aria-label="Status">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                @else
                    <select wire:model.live="ledgerType" class="fi-input text-sm" aria-label="Ledger type">
                        <option value="">Credits and debits</option>
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                    </select>
                @endif
                <input wire:model.live="dateFrom" type="date" class="fi-input text-sm" aria-label="From date">
                <input wire:model.live="dateTo" type="date" class="fi-input text-sm" aria-label="To date">
            </div>
        @endif

        @if($section === 'transactions')
            <div class="fi-card overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            @foreach(['Date / time','FinPe reference','Merchant reference','Provider reference','RRN','Payout provider','Amount','Charge','Total debited','Beneficiary','Status',''] as $col)
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $txn)
                            <tr class="cursor-pointer hover:bg-slate-50" wire:click="show({{ $txn->id }})">
                                <td class="px-3 py-3 text-xs text-slate-600">{{ $txn->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-violet-700">{{ $txn->reference }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->merchant_ref ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->bank_reference ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->rrn ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-slate-900">{{ $this->providerName($txn->payout_provider) }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900">₹{{ number_format((float) $txn->amount, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900">₹{{ number_format((float) $txn->payout_charge, 2) }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-slate-900">₹{{ number_format((float) $txn->amount + (float) $txn->payout_charge, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->beneficiary_name ?: '—' }} · {{ $this->maskAccount($txn->account_number) }}</td>
                                <td class="px-3 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $this->statusBadgeClass($txn->status) }}">{{ ucfirst((string) $txn->status) }}</span></td>
                                <td class="px-3 py-3">@include('livewire.vendor.partials.txn-actions', ['id' => $txn->id, 'reference' => $txn->reference, 'type' => $txn->type, 'livewire' => true])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-5 py-16 text-center text-sm text-slate-500">No payout transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="border-t border-slate-100 px-6 py-4">{{ $transactions->links() }}</div>
            </div>
        @endif

        @if($section === 'wallet')
            <div class="fi-card overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            @foreach(['Date / time','Type','Wallet amount','Opening balance','Closing balance','Payout amount','Charge','FinPe reference','Payout provider','Status',''] as $col)
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ledger as $row)
                            @php $payout = $payoutByReference->get($row->reference); @endphp
                            <tr>
                                <td class="px-3 py-3 text-xs text-slate-600">{{ $row->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-3 py-3 text-sm font-semibold {{ $row->type === 'credit' ? 'text-emerald-700' : 'text-slate-900' }}">{{ ucfirst((string) $row->type) }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-slate-900">₹{{ number_format((float) $row->amount, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700">₹{{ number_format((float) $row->balance_before, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700">₹{{ number_format((float) $row->balance_after, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900">{{ $payout ? '₹'.number_format((float) $payout->amount, 2) : '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900">{{ $payout ? '₹'.number_format((float) $payout->payout_charge, 2) : '—' }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-violet-700">{{ $row->reference ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-900">{{ $payout ? $this->providerName($payout->payout_provider) : '—' }}</td>
                                <td class="px-3 py-3">
                                    @if($payout)
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $this->statusBadgeClass($payout->status) }}">{{ ucfirst((string) $payout->status) }}</span>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">@include('livewire.vendor.partials.txn-actions', ['id' => $payout?->id, 'reference' => $payout?->reference, 'type' => $payout ? 'payout' : null, 'livewire' => true])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-16 text-center text-sm text-slate-500">No wallet ledger entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="border-t border-slate-100 px-6 py-4">{{ $ledger->links() }}</div>
            </div>
        @endif

        @if($section === 'docs')
            @include('livewire.vendor.partials.payout-api-docs')
        @endif

    @endunless

    @if($enabled && $detail)
        @php
            $charge = (float) $detail->payout_charge;
            $total = round((float) $detail->amount + $charge, 2);
            $bank = trim(($detail->bank_name ?: '').($detail->bank_name && $detail->beneficiary_bank_code ? ' · ' : '').($detail->beneficiary_bank_code ?: ''));
        @endphp
        <div class="fixed inset-0 flex items-center justify-center overflow-hidden bg-slate-950/55 px-3 sm:px-6" style="z-index: 100; padding-top: 4.75rem; padding-bottom: 1rem;" wire:click.self="closeDetail" wire:key="vendor-payout-detail-{{ $detail->id }}">
            <div class="flex min-h-0 w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5" style="max-height: min(90vh, calc(100vh - 4.75rem - 1rem));" role="dialog" aria-labelledby="vendor-payout-detail-title">
                <div class="shrink-0 bg-slate-900 px-5 py-4 text-white sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5M8 13h8M8 17h5"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h2 id="vendor-payout-detail-title" class="text-lg font-bold tracking-tight sm:text-xl">Payout Transaction Details</h2>
                                <p class="mt-0.5 text-sm text-slate-300">Complete information of the payout transaction</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold ring-1 ring-inset {{ $this->statusBadgeClass($detail->status) }}">{{ ucfirst((string) $detail->status) }}</span>
                            <button type="button" class="rounded-lg p-1.5 text-slate-200 hover:bg-white/10" wire:click="closeDetail" aria-label="Close">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-3 border-t border-white/10 pt-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-300">FinPe reference</p>
                                <p class="mt-0.5 break-all text-sm font-semibold">{{ $detail->reference }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-300">Vendor</p>
                                <p class="mt-0.5 truncate text-sm font-semibold">{{ auth('vendor')->user()->business_name ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-300">Date / time</p>
                                <p class="mt-0.5 text-sm font-semibold">{{ $detail->created_at?->format('d M Y, h:i A') ?: '—' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('vendor.payout-transactions.receipt', $detail->reference) }}" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; align-self:flex-start; height:30px; padding:0 10px; font-size:12px; text-decoration:none;">
                            Download Receipt
                        </a>
                    </div>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-5 py-5 sm:px-6">
                    <div class="grid grid-cols-2 gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm lg:grid-cols-4">
                        <div class="px-2 py-2"><p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout amount</p><p class="mt-1 text-xl font-bold text-slate-900">₹{{ number_format((float) $detail->amount, 2) }}</p></div>
                        <div class="px-2 py-2"><p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Commission / charge</p><p class="mt-1 text-xl font-bold text-slate-900">₹{{ number_format($charge, 2) }}</p></div>
                        <div class="rounded-xl bg-violet-50 px-3 py-2"><p class="text-[11px] font-semibold uppercase tracking-wider text-violet-700">Total debited</p><p class="mt-1 text-xl font-bold text-violet-950">₹{{ number_format($total, 2) }}</p></div>
                        <div class="px-2 py-2"><p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status</p><p class="mt-2"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $this->statusBadgeClass($detail->status) }}">{{ ucfirst((string) $detail->status) }}</span></p></div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <h3 class="border-b border-violet-100 bg-violet-50 px-4 py-3 text-sm font-bold text-slate-900">Beneficiary details</h3>
                            <dl class="grid grid-cols-1 gap-px bg-slate-100 sm:grid-cols-2">
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Name</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->beneficiary_name ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Account</dt><dd class="mt-1 font-mono text-sm font-bold text-slate-900">{{ $this->maskAccount($detail->account_number) }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">IFSC</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->ifsc_code ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Bank</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $bank !== '' ? $bank : '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Mobile</dt><dd class="mt-1 font-mono text-sm font-bold text-slate-900">{{ $this->maskMobile($detail->beneficiary_mobile) }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">State</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->beneficiary_location ?: '—' }}</dd></div>
                            </dl>
                        </section>
                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <h3 class="border-b border-violet-100 bg-violet-50 px-4 py-3 text-sm font-bold text-slate-900">Transaction details</h3>
                            <dl class="grid grid-cols-1 gap-px bg-slate-100 sm:grid-cols-2">
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">FinPe reference</dt><dd class="mt-1 break-all text-sm font-bold text-violet-800">{{ $detail->reference }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Merchant reference</dt><dd class="mt-1 break-all text-sm font-bold text-slate-900">{{ $detail->merchant_ref ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Provider reference</dt><dd class="mt-1 break-all text-sm font-bold text-slate-900">{{ $detail->bank_reference ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">RRN</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->rrn ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Payout provider</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $this->providerName($detail->payout_provider) }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Payment mode</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ strtoupper((string) $detail->service) ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Payment purpose</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->payment_purpose ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Provider message</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->failure_reason ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Created</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->created_at?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                                <div class="bg-white px-4 py-3"><dt class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Updated</dt><dd class="mt-1 text-sm font-bold text-slate-900">{{ $detail->updated_at?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                            </dl>
                        </section>
                    </div>
                </div>
                <div class="flex shrink-0 items-center justify-between gap-3 border-t border-slate-200 bg-white px-5 py-3.5 sm:px-6">
                    <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" wire:click="closeDetail">Close</button>
                    <a href="{{ route('vendor.payout-transactions.receipt', $detail->reference) }}" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:30px; padding:0 10px; font-size:12px; text-decoration:none;">
                        Download Receipt
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
