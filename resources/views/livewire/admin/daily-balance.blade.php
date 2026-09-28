<div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Daily balance</h1>
            <p class="mt-1 text-sm text-slate-500">Opening of a day is the previous day's closing. Closing = opening + credit − debit.</p>
        </div>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Sample data</span>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            [$singleDay ? 'Opening' : 'Opening (period start)', $report['opening'], 'text-slate-900'],
            ['Credit', $report['credit'], 'text-emerald-700'],
            ['Debit', $report['debit'], 'text-red-700'],
            [$singleDay ? 'Closing' : 'Closing (period end)', $report['closing'], 'text-blue-800'],
        ] as [$label, $value, $color])
            <div class="fi-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold {{ $color }}">₹{{ number_format($value, 2) }}</p>
            </div>
        @endforeach
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px;">
            <div style="width:260px; max-width:100%;">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Vendor</label>
                <select wire:model.live="vendor" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                    <option value="">All vendors</option>
                    @foreach($vendors as $item)
                        <option value="{{ $item['code'] }}">{{ $item['name'] }} ({{ $item['code'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">From</label>
                <input type="date" wire:model.live="dateFrom" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">To</label>
                <input type="date" wire:model.live="dateTo" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Check a date</label>
                <input type="date" wire:model.live="onDate" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div style="display:flex; gap:8px; margin-left:auto; flex-wrap:wrap;">
                <button type="button" wire:click="yesterday" class="fi-btn fi-btn-secondary">Yesterday</button>
                <button type="button" wire:click="thisMonth" class="fi-btn fi-btn-secondary">This month</button>
                <button type="button" wire:click="previousMonth" class="fi-btn fi-btn-secondary">Previous month</button>
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-primary">Export Excel</button>
            </div>
        </div>
    </div>

    <p class="mb-5 text-sm text-slate-500">A day with no transaction keeps the same opening and closing. Period opening is the first day's opening. Period closing is the last day's closing.</p>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['Date','Opening','Credit','Debit','Closing'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['days'] as $day)
                        <tr class="{{ $day['date'] === now()->subDay()->toDateString() ? 'bg-blue-50' : '' }}">
                            <td class="px-5 py-3 text-sm font-medium text-slate-800">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-sm text-slate-800">₹{{ number_format($day['opening'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-semibold text-emerald-700">₹{{ number_format($day['credit'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-semibold text-red-700">₹{{ number_format($day['debit'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-bold text-slate-900">₹{{ number_format($day['closing'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No dates in this range.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($report['days']->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">Period</td>
                            <td class="px-5 py-3 text-sm font-semibold">₹{{ number_format($report['opening'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-semibold text-emerald-700">₹{{ number_format($report['credit'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-semibold text-red-700">₹{{ number_format($report['debit'], 2) }}</td>
                            <td class="px-5 py-3 text-sm font-bold">₹{{ number_format($report['closing'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
