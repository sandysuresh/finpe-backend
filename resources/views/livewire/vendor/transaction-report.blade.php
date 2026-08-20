<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Transaction Report</h1>
            <p class="mt-1 text-sm text-slate-500">Full history of all your payment transactions.</p>
        </div>
        <button class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
        </button>
    </div>

    {{-- Summary cards --}}
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
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-700 text-white">
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

    {{-- Filters --}}
    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       class="fi-input pl-9 text-sm w-52"
                       placeholder="Reference, beneficiary...">
            </div>
            <select wire:model.live="status"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-violet-400">
                <option value="">All Status</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
            </select>
            <select wire:model.live="service"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-violet-400">
                <option value="">All Services</option>
                <option value="imps">IMPS</option>
                <option value="neft">NEFT</option>
                <option value="rtgs">RTGS</option>
            </select>
            <input type="date" wire:model.live="dateFrom"
                   class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-violet-400">
            <span class="text-xs text-slate-400">to</span>
            <input type="date" wire:model.live="dateTo"
                   class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-violet-400">
            <button wire:click="resetFilters"
                    class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="fi-card overflow-hidden">
        @if($transactions->isEmpty())
            {{-- ── BEAUTIFUL EMPTY STATE ── --}}
            <div class="flex flex-col items-center justify-center px-6 py-24 text-center">
                {{-- Illustration --}}
                <div class="relative mb-6">
                    <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-slate-100">
                        <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    {{-- Floating dots --}}
                    <div class="absolute -right-3 -top-3 h-5 w-5 rounded-full border-2 border-slate-200 bg-white"></div>
                    <div class="absolute -bottom-2 -left-2 h-3.5 w-3.5 rounded-full bg-violet-200"></div>
                    <div class="absolute -right-5 bottom-4 h-2.5 w-2.5 rounded-full bg-amber-200"></div>
                </div>

                @if($search || $status || $service || $dateFrom || $dateTo)
                    {{-- Filtered empty state --}}
                    <h3 class="text-base font-semibold text-slate-800">No transactions match your filters</h3>
                    <p class="mt-2 max-w-sm text-sm text-slate-400">
                        Try clearing your filters or adjusting the date range to find what you're looking for.
                    </p>
                    <button wire:click="resetFilters"
                            class="mt-5 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Clear All Filters
                    </button>
                @else
                    {{-- No records at all --}}
                    <h3 class="text-base font-semibold text-slate-800">No transactions yet</h3>
                    <p class="mt-2 max-w-sm text-sm text-slate-400">
                        Once you start sending money, all your transactions will appear here with full details and status tracking.
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <a href="{{ route('vendor.send-money') }}"
                           class="flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Money
                        </a>
                        <a href="{{ route('vendor.wallet') }}"
                           class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Add Money First
                        </a>
                    </div>
                    {{-- Step guide --}}
                    <div class="mt-8 grid max-w-lg grid-cols-3 gap-4">
                        @foreach([
                            ['1','Add Money','Top up your wallet with a request to admin'],
                            ['2','Send Money','Transfer funds to any bank account'],
                            ['3','Track Here','All transactions appear in this report'],
                        ] as [$n,$t,$d])
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                <div class="mx-auto mb-2 flex h-7 w-7 items-center justify-center rounded-full bg-violet-600 text-xs font-bold text-white">{{ $n }}</div>
                                <p class="text-xs font-semibold text-slate-700">{{ $t }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Reference','Beneficiary','Amount','Service','Status','Date'] as $col)
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
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-slate-800">{{ $tx->reference }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600">{{ $tx->beneficiary_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900">₹{{ number_format((float)$tx->amount,2) }}</td>
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
            <div class="border-t border-slate-100 px-6 py-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
