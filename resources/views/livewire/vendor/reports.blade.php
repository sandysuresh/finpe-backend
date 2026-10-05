<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
        <p class="mt-1 text-sm text-slate-500">Wallet ledger and transactions for your account.</p>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            ['Opening balance', $summary['opening'], 'text-slate-900'],
            ['Credit', $summary['credit'], 'text-emerald-700'],
            ['Debit', $summary['debit'], 'text-red-700'],
            ['Closing balance', $summary['closing'], 'text-violet-800'],
            ['Transactions', $summary['transactions'], 'text-slate-900'],
            ['Transaction amount', $summary['payout_amount'], 'text-slate-900'],
            ['Charge', $summary['payout_charge'], 'text-slate-900'],
        ] as [$label, $value, $color])
            <div class="fi-card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold {{ $color }}">
                    @if($label === 'Transactions')
                        {{ number_format($value) }}
                    @else
                        ₹{{ number_format($value, 2) }}
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px;">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">From</label>
                <input type="date" wire:model.live="dateFrom" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">To</label>
                <input type="date" wire:model.live="dateTo" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Ledger type</label>
                <select wire:model.live="ledgerType" class="fi-input text-sm" style="height:40px; width:160px;">
                    <option value="">Credit and debit</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Status</label>
                <select wire:model.live="status" class="fi-input text-sm" style="height:40px; width:150px;">
                    <option value="">All statuses</option>
                    <option value="success">Success</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Transaction type</label>
                <select wire:model.live="txnType" class="fi-input text-sm" style="height:40px; width:160px;">
                    <option value="">All types</option>
                    @foreach($txnTypes as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:8px; margin-left:auto;">
                <button type="button" wire:click="resetFilters" class="fi-btn fi-btn-secondary">Reset</button>
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-primary">Export Excel</button>
            </div>
        </div>
    </div>

    <p class="mb-5 text-sm text-slate-500">Opening is the balance before the first matching ledger entry. Closing is the balance after the last matching entry. Ledger type filters the wallet table. Date, status, and transaction type filter both tables.</p>

    <div class="fi-card mb-5 overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Wallet ledger</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['Date / time','Reference','Description','Type','Amount','Opening','Closing','Payout amount','Charge','Status',''] as $col)
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledger as $line)
                        @php $txn = $payouts->get($line->reference); @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $line->created_at?->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-violet-700">{{ $line->reference ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $line->description ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs font-semibold {{ $line->type === 'credit' ? 'text-emerald-700' : 'text-red-700' }}">{{ ucfirst((string) $line->type) }}</td>
                            <td class="px-4 py-3 text-sm">₹{{ number_format((float) $line->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">₹{{ number_format((float) $line->balance_before, 2) }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">₹{{ number_format((float) $line->balance_after, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $txn ? '₹'.number_format((float) $txn->amount, 2) : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $txn ? '₹'.number_format((float) $txn->payout_charge, 2) : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $txn ? ucfirst((string) $txn->status) : '—' }}</td>
                            <td class="px-4 py-3">@include('livewire.vendor.partials.txn-actions', ['id' => $txn?->id, 'reference' => $txn?->reference, 'type' => $txn?->type])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center text-sm text-slate-500">No ledger entries for these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $ledger->links() }}</div>
    </div>

    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['Date / time','FinPe reference','Merchant reference','Type','Service','Amount','Charge','Beneficiary','Status',''] as $col)
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $txn)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $txn->created_at?->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-violet-700">{{ $txn->reference }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $txn->merchant_ref ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ ucfirst((string) $txn->type) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ strtoupper((string) $txn->service) }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">₹{{ number_format((float) $txn->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">₹{{ number_format((float) $txn->payout_charge, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $txn->beneficiary_name ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ ucfirst((string) $txn->status) }}</td>
                            <td class="px-4 py-3">@include('livewire.vendor.partials.txn-actions', ['id' => $txn->id, 'reference' => $txn->reference, 'type' => $txn->type])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center text-sm text-slate-500">No transactions for these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
    </div>
</div>
