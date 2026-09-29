<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Merchant Commission</h1>
        <p class="mt-1 text-sm text-slate-500">Applied commission for entries that include a merchant. Payouts without a merchant stay under Agent Commission.</p>
    </div>
    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-4">
        <select wire:model.live="vendorId" class="fi-input text-sm"><option value="">Vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>@endforeach</select>
        <select wire:model.live="merchantId" class="fi-input text-sm"><option value="">Merchant</option>@foreach($merchantOptions as $merchant)<option value="{{ $merchant->id }}">{{ $merchant->code }}</option>@endforeach</select>
        <input wire:model.live="dateFrom" type="date" class="fi-input text-sm">
        <input wire:model.live="dateTo" type="date" class="fi-input text-sm">
    </div>
    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead><tr>@foreach(['Merchant','Code','Partner','Commission entry count','Total commission'] as $col)<th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                    @php($merchant = $merchants[$row->merchant_id] ?? null)
                    <tr>
                        <td class="px-5 py-3 text-sm font-semibold"><a class="text-blue-700" href="{{ route('admin.commission.history', ['merchant' => $row->merchant_id]) }}">{{ trim(($merchant->first_name ?? '').' '.($merchant->last_name ?? '')) ?: 'Merchant' }}</a></td>
                        <td class="px-5 py-3 text-sm">{{ $merchant->code ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm">{{ $vendorNames[$row->vendor_id] ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm">{{ $row->entry_count }}</td>
                        <td class="px-5 py-3 text-sm">₹{{ number_format((float) $row->total_commission, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">No merchant commission entries.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($merchantId !== '')
        <div class="mt-6">
            <h2 class="mb-3 text-lg font-bold text-slate-900">Applied entries</h2>
            <div class="fi-card overflow-x-auto">
                <table class="min-w-full">
                    <thead><tr>@foreach(['Reference','Transaction date','Partner','Merchant','Service','Amount','Rate','Commission','Status','Commission date'] as $col)<th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($entries as $entry)
                            <tr>
                                <td class="px-3 py-3 text-sm">{{ $entry->source_reference ?: '—' }}</td>
                                <td class="px-3 py-3 text-xs">{{ $entry->source_type === 'transactions' ? ($entry->sourceTransaction?->created_at?->format('d M Y, h:i A') ?? '—') : '—' }}</td>
                                <td class="px-3 py-3 text-sm">{{ $entry->vendor_name_snapshot ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm">{{ $entry->merchant_code_snapshot ?: ($entry->merchant->code ?? '—') }}</td>
                                <td class="px-3 py-3 text-sm">{{ $entry->service_snapshot ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm">₹{{ number_format((float) $entry->base_amount, 2) }}</td>
                                <td class="px-3 py-3 text-sm">{{ $entry->calc_type === 'percentage' ? number_format((float) $entry->rate_value, 2).'%' : '₹'.number_format((float) $entry->rate_value, 2) }}</td>
                                <td class="px-3 py-3 text-sm font-semibold">₹{{ number_format((float) $entry->commission_amount, 2) }}</td>
                                <td class="px-3 py-3 text-sm">{{ ucfirst((string) $entry->status) }}</td>
                                <td class="px-3 py-3 text-xs">{{ $entry->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">No commission entries for this merchant.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
