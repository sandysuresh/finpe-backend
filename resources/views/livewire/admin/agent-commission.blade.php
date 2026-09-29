<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Agent Commission</h1>
        <p class="mt-1 text-sm text-slate-500">Applied commission recorded for each partner. Totals come from commission entries, not the partner’s configured rate.</p>
    </div>
    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-3">
        <select wire:model.live="vendorId" class="fi-input text-sm"><option value="">All partners</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>@endforeach</select>
        <input wire:model.live="dateFrom" type="date" class="fi-input text-sm">
        <input wire:model.live="dateTo" type="date" class="fi-input text-sm">
    </div>
    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead><tr>@foreach(['Partner','Commission entry count','Total commission amount'] as $col)<th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                    <tr>
                        <td class="px-5 py-3 text-sm font-semibold"><a class="text-blue-700" href="{{ route('admin.commission.history', ['vendor' => $row->vendor_id]) }}">{{ $names[$row->vendor_id] ?? 'Partner' }}</a></td>
                        <td class="px-5 py-3 text-sm">{{ $row->entry_count }}</td>
                        <td class="px-5 py-3 text-sm">₹{{ number_format((float) $row->total_commission, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">No applied commission yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($vendorId !== '')
        <div class="mt-6">
            <h2 class="mb-3 text-lg font-bold text-slate-900">Applied entries</h2>
            <div class="fi-card overflow-x-auto">
                <table class="min-w-full">
                    <thead><tr>@foreach(['Reference','Transaction date','Service','Payout amount','Rate','Commission','Status','Commission date'] as $col)<th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($entries as $entry)
                            <tr>
                                <td class="px-3 py-3 text-sm">{{ $entry->source_reference ?: '—' }}</td>
                                <td class="px-3 py-3 text-xs">{{ $entry->source_type === 'transactions' ? ($entry->sourceTransaction?->created_at?->format('d M Y, h:i A') ?? '—') : '—' }}</td>
                                <td class="px-3 py-3 text-sm">{{ $entry->service_snapshot ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm">₹{{ number_format((float) $entry->base_amount, 2) }}</td>
                                <td class="px-3 py-3 text-sm">{{ $entry->calc_type === 'percentage' ? number_format((float) $entry->rate_value, 2).'%' : '₹'.number_format((float) $entry->rate_value, 2) }}</td>
                                <td class="px-3 py-3 text-sm font-semibold">₹{{ number_format((float) $entry->commission_amount, 2) }}</td>
                                <td class="px-3 py-3 text-sm">{{ ucfirst((string) $entry->status) }}</td>
                                <td class="px-3 py-3 text-xs">{{ $entry->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">No commission entries for this partner.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
