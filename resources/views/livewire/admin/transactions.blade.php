<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Transactions</h1>
            <p class="mt-1 text-sm text-slate-500">All vendor payouts across IMPS, NEFT and RTGS.</p>
        </div>
        @if($usingSample)
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Sample data</span>
        @endif
    </div>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach([
            ['Total',   $summary['total'],   'text-slate-900',   'bg-slate-800',   'text-white'],
            ['Success', $summary['success'], 'text-emerald-700', 'bg-emerald-700','text-white'],
            ['Failed',  $summary['failed'],  'text-red-700',     'bg-red-600',    'text-white'],
            ['Pending', $summary['pending'], 'text-amber-700',   'bg-amber-600',  'text-white'],
        ] as [$l,$v,$vc,$bg,$ic])
            <div class="fi-card flex items-center gap-4 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $bg }}">
                    <span class="text-sm font-bold {{ $ic }}">{{ number_format($v) }}</span>
                </div>
                <div>
                    <p class="text-xs text-slate-400">{{ $l }}</p>
                    <p class="text-base font-bold {{ $vc }}">{{ number_format($v) }}</p>
                </div>
            </div>
        @endforeach
        <div class="fi-card flex items-center gap-4 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-700 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400">Success Vol.</p>
                <p class="text-base font-bold text-slate-900">₹{{ $summary['volume'] }}</p>
            </div>
        </div>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       class="fi-input pl-9 text-sm w-56"
                       placeholder="Vendor, reference, beneficiary...">
            </div>
            <select wire:model.live="status"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-blue-400">
                <option value="">All Status</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
            </select>
            <select wire:model.live="service"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-blue-400">
                <option value="">All Services</option>
                <option value="imps">IMPS</option>
                <option value="neft">NEFT</option>
                <option value="rtgs">RTGS</option>
            </select>
            <input type="date" wire:model.live="dateFrom"
                   class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-400">
            <span class="text-xs text-slate-400">to</span>
            <input type="date" wire:model.live="dateTo"
                   class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-400">
            <button wire:click="resetFilters"
                    class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50">
                Reset
            </button>
        </div>
    </div>

    <div class="fi-card overflow-hidden">
        @if($transactions->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-24 text-center">
                <h3 class="text-base font-semibold text-slate-800">No transactions match your filters</h3>
                <p class="mt-2 max-w-sm text-sm text-slate-400">Try clearing filters or adjusting the date range.</p>
                <button wire:click="resetFilters"
                        class="mt-5 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Clear All Filters
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Reference','Vendor','Beneficiary','Amount','Service','Status','Date'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($transactions as $tx)
                            @php
                                $sc = match($tx->status) {
                                    'success' => 'bg-emerald-50 text-emerald-700',
                                    'failed'  => 'bg-red-50 text-red-600',
                                    default   => 'bg-amber-50 text-amber-700',
                                };
                                $vendor = $tx->vendor ?? null;
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-slate-800">{{ $tx->reference }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @if($vendor)
                                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="text-sm font-semibold text-blue-700 hover:underline">
                                            {{ $vendor->business_name }}
                                        </a>
                                        <p class="text-xs text-slate-400">{{ $vendor->vendor_code }}</p>
                                    @else
                                        <p class="text-sm font-semibold text-slate-800">{{ $tx->vendor_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">{{ $tx->vendor_code ?? '' }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600">{{ $tx->beneficiary_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900">₹{{ number_format((float)$tx->amount, 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ strtoupper($tx->service) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $sc }}">{{ ucfirst($tx->status) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-400">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
