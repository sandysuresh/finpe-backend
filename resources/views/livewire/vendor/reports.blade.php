<div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wallet report</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $report['name'] }} · <span class="font-mono">{{ $report['code'] }}</span></p>
        </div>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Sample data</span>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            ['Opening balance', $report['opening'], 'text-slate-900'],
            ['Credit', $report['credit'], 'text-emerald-700'],
            ['Debit', $report['debit'], 'text-red-700'],
            ['Closing balance', $report['closing'], 'text-violet-800'],
        ] as [$label, $value, $color])
            <div class="fi-card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold {{ $color }}">₹{{ number_format($value, 2) }}</p>
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
            <div style="display:flex; gap:8px; margin-left:auto;">
                <button type="button" wire:click="resetFilters" class="fi-btn fi-btn-secondary">Reset</button>
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-primary">Export Excel</button>
            </div>
        </div>
    </div>

    <p class="mb-5 text-sm text-slate-500">Closing balance = opening + credit − debit.</p>

    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Ledger</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['Date','Reference','Description','Type','Amount','Balance before','Balance after'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['lines'] as $line)
                        <tr>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ \Illuminate\Support\Carbon::parse($line['date'])->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700">{{ $line['reference'] }}</td>
                            <td class="px-5 py-3 text-sm text-slate-700">{{ $line['description'] }}</td>
                            <td class="px-5 py-3 text-xs font-semibold {{ $line['type'] === 'credit' ? 'text-emerald-700' : 'text-red-700' }}">{{ ucfirst($line['type']) }}</td>
                            <td class="px-5 py-3 text-sm">₹{{ number_format($line['amount'], 2) }}</td>
                            <td class="px-5 py-3 text-sm text-slate-600">₹{{ number_format($line['balance_before'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">₹{{ number_format($line['balance_after'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-500">No ledger lines in this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
