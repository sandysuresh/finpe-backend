<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Merchants</h1>
        <p class="mt-1 text-sm text-slate-500">AePS merchants registered under a partner.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search merchant or partner...">
    </div>

    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead>
                <tr>
                    @foreach(['Merchant','Partner','Phone','Status','Applied entries','Applied total'] as $col)
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($merchants as $merchant)
                    <tr>
                        <td class="px-5 py-3 text-sm">
                            <div class="font-semibold text-slate-900">{{ trim($merchant->first_name.' '.$merchant->last_name) ?: $merchant->code }}</div>
                            <div class="text-xs text-slate-500">{{ $merchant->code }}</div>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-700">{{ $merchant->vendor->business_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-700">{{ $merchant->phone_masked ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-700">{{ $merchant->onboarding_status ?: '—' }}</td>
                        <td class="px-5 py-3 text-sm text-slate-700">{{ $applied[$merchant->id]->entry_count ?? 0 }}</td>
                        <td class="px-5 py-3 text-sm text-slate-900">₹{{ number_format((float) ($applied[$merchant->id]->total_commission ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-sm text-slate-500">No merchants yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $merchants->links() }}</div>
    </div>
</div>
