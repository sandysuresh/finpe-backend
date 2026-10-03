<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Commission Summary</h1>
        <p class="mt-1 text-sm text-slate-500">Recorded commission grouped by vendor and payout provider.</p>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="fi-card border-t-4 border-blue-700 p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total commission</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">₹ {{ number_format($total, 2) }}</p>
        </div>
        <div class="fi-card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Vendor groups</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($rows->count()) }}</p>
        </div>
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        @foreach(['Vendor','Payout Provider','Payout Transactions','Total Commission'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="text-sm font-semibold text-slate-900">{{ $row->business_name ?: '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $row->vendor_code ?: '—' }}</p>
                            </td>
                            <td class="px-5 py-3">
                                @if($row->provider)
                                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{{ \App\Support\CommissionProviders::name($row->provider) }}</span>
                                @else
                                    <span class="text-sm text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-800">{{ number_format((int) $row->payout_count) }}</td>
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">₹ {{ number_format((float) $row->total_commission, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center text-sm text-slate-500">No recorded commission yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
