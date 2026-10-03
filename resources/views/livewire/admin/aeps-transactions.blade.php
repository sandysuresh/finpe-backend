<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">AePS Transactions</h1>
        <p class="mt-1 text-sm text-slate-500">Read-only AePS transactions. This screen does not change wallets or commission.</p>
    </div>

    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-3 xl:grid-cols-6">
        <select wire:model.live="vendorId" class="fi-input text-sm">
            <option value="">Vendor</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>
            @endforeach
        </select>
        <select wire:model.live="merchantId" class="fi-input text-sm">
            <option value="">Merchant</option>
            @foreach($merchants as $merchant)
                <option value="{{ $merchant->id }}">{{ $merchant->code }}</option>
            @endforeach
        </select>
        <select wire:model.live="service" class="fi-input text-sm">
            <option value="">Service</option>
            @foreach($services as $service)
                <option value="{{ $service }}">{{ $this->serviceLabel($service) }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="fi-input text-sm">
            <option value="">Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFrom" type="date" class="fi-input text-sm" aria-label="From date">
        <input wire:model.live="dateTo" type="date" class="fi-input text-sm" aria-label="To date">
    </div>

    <div class="fi-card overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    @foreach(['Vendor','Merchant','Service','Amount','FinPe reference','Provider reference / RRN','Status','Provider status','Created'] as $col)
                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $txn)
                    <tr class="cursor-pointer hover:bg-slate-50" wire:click="show({{ $txn->id }})">
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->vendor->business_name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm">
                            <div class="font-semibold text-slate-900">{{ $txn->merchant->code ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ trim(($txn->merchant->first_name ?? '').' '.($txn->merchant->last_name ?? '')) }}</div>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $this->serviceLabel($txn->service) }}</td>
                        <td class="px-3 py-3 text-sm text-slate-900">₹{{ number_format((float) $txn->amount, 2) }}</td>
                        <td class="px-3 py-3 text-sm font-semibold text-blue-700">{{ $txn->reference }}</td>
                        <td class="px-3 py-3 text-xs text-slate-700">
                            <div>{{ $txn->provider_txn_ref ?: '—' }}</div>
                            <div>{{ $txn->rrn ?: '—' }}</div>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ str_replace('_', ' ', (string) $txn->status) }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $txn->provider_status_code ?: '—' }}</td>
                        <td class="px-3 py-3 text-xs text-slate-600">{{ $txn->created_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-16 text-center text-sm text-slate-500">No AePS transactions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $transactions->links() }}</div>
    </div>

    @if($detail)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4" wire:click.self="$set('detailId', null)">
            <div class="fi-card max-h-[85vh] w-full max-w-2xl overflow-y-auto p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-bold text-slate-900">{{ $detail->reference }}</h2>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.aeps-transactions.receipt', $detail->reference) }}" class="text-sm font-semibold text-blue-700">Download Receipt PDF</a>
                        <button type="button" wire:click="$set('detailId', null)" class="text-sm font-semibold text-slate-500">Close</button>
                    </div>
                </div>
                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <div class="md:col-span-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Transaction Details</div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Service</dt><dd>{{ $this->serviceLabel($detail->service) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd>₹{{ number_format((float) $detail->amount, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">FinPe reference</dt><dd>{{ $detail->reference }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Aadhaar</dt><dd>{{ \App\Services\Aeps\AepsPayloadSanitizer::displayAadhaar($detail->aadhaar_masked) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>{{ str_replace('_', ' ', (string) $detail->status) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Created</dt><dd>{{ $detail->created_at?->format('d M Y, h:i A') }}</dd></div>
                    <div class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Merchant and vendor</div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Vendor</dt><dd>{{ $detail->vendor->business_name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Merchant</dt><dd>{{ $detail->merchant->code ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Client reference</dt><dd>{{ $detail->client_reference }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Provider reference</dt><dd class="text-right">{{ $detail->provider_txn_ref ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">RRN</dt><dd>{{ $detail->rrn ?: '—' }}</dd></div>
                    <div class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Provider details</div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Provider status</dt><dd>{{ $detail->provider_status_code ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Provider message</dt><dd class="text-right">{{ $detail->provider_status_description ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">NPCI</dt><dd class="text-right">{{ trim(($detail->npci_code ?: '').' '.($detail->npci_message ?: '')) ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Bank IIN</dt><dd>{{ $detail->bank_iin ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Failure reason</dt><dd class="text-right">{{ $detail->failure_reason ?: '—' }}</dd></div>
                </dl>
            </div>
        </div>
    @endif
</div>
