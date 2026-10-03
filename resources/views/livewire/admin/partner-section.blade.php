<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $meta['title'] }}</h1>
        <p class="mt-1 text-sm text-slate-500">Partners → {{ $meta['title'] }}. {{ $meta['text'] }}</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search partner or reference...">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @if($section === 'transactions')
                            @foreach(['Reference','Partner','Amount','Commission','Status','Time'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                            @endforeach
                        @elseif($section === 'wallet')
                            @foreach(['Partner','Code','Balance','Hold'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                            @endforeach
                        @elseif($section === 'commission')
                            @foreach(['Vendor','Payout Provider','Type','Rate','Commission'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                            @endforeach
                        @elseif($section === 'settlement')
                            @foreach(['Reference','Partner','Amount','Net','Status','Settled'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                            @endforeach
                        @else
                            @foreach(['Partner','Code','KYC','Comment','Reviewed'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr>
                            @if($section === 'transactions')
                                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $row->reference }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $row->vendor->business_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ {{ number_format((float) $row->amount, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-slate-800">{{ $row->commissionEntry ? '₹'.number_format((float) $row->commissionEntry->commission_amount, 2) : '—' }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ ucfirst((string) $row->status) }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $row->created_at?->format('d M Y, h:i A') }}</td>
                            @elseif($section === 'wallet')
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $row->business_name }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $row->vendor_code }}</td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ {{ number_format((float) ($row->wallet->balance ?? 0), 2) }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">₹ {{ number_format((float) ($row->wallet->hold_balance ?? 0), 2) }}</td>
                            @elseif($section === 'commission')
                                @php($rule = $configured[$row->id] ?? null)
                                <td class="px-5 py-3">
                                    <p class="text-sm font-semibold text-slate-900">{{ $row->business_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $row->vendor_code }}</p>
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    @if($rule && $rule->provider)
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{{ \App\Support\CommissionProviders::name($rule->provider) }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $rule ? ucfirst((string) $rule->type) : '—' }}</td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">@if(! $rule)—@elseif($rule->calc_type === 'percentage'){{ number_format((float) $rule->value, 2) }}%@else₹{{ number_format((float) $rule->value, 2) }}@endif</td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">₹{{ number_format((float) ($applied[$row->id]->total_commission ?? 0), 2) }}</td>
                            @elseif($section === 'settlement')
                                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $row->reference }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $row->vendor->business_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ {{ number_format((float) $row->amount, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ {{ number_format((float) $row->net_amount, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ ucfirst((string) $row->status) }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $row->settled_at?->format('d M Y, h:i A') ?: '—' }}</td>
                            @else
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $row->business_name }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $row->vendor_code }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ ucfirst((string) $row->kyc_status) }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $row->kyc_comment ?: '—' }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $row->kyc_reviewed_at?->format('d M Y, h:i A') ?: '—' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-sm text-slate-500">No records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $rows->links() }}</div>
    </div>
</div>
