@php($processed = $selected->processedAt())
<dl class="grid gap-3 text-sm sm:grid-cols-2">
    <div><dt class="text-slate-500">FinPe transaction ID</dt><dd class="font-medium">{{ $selected->id }}</dd></div>
    <div><dt class="text-slate-500">AlliancePe payout ID</dt><dd>{{ $selected->provider_payout_id ?: '—' }}</dd></div>
    <div><dt class="text-slate-500">Merchant order ID</dt><dd>{{ $selected->merchant_order_id }}</dd></div>
    <div><dt class="text-slate-500">Amount</dt><dd>₹{{ number_format((float) $selected->amount, 2) }}</dd></div>
    <div><dt class="text-slate-500">FinPe commission</dt><dd>{{ $selected->commission_type ? $selected->commission_type.' '.$selected->commission_rate : '—' }} · ₹{{ number_format((float) $selected->commission_amount, 2) }}</dd></div>
    <div><dt class="text-slate-500">Total wallet debit</dt><dd>₹{{ number_format((float) ($selected->total_debited ?? $selected->amount), 2) }}</dd></div>
    @if(!empty($showVendor))
        <div><dt class="text-slate-500">Vendor</dt><dd>{{ $selected->vendor?->business_name ?: '—' }} ({{ $selected->vendor_id }})</dd></div>
        <div><dt class="text-slate-500">Environment</dt><dd>{{ $selected->environment ? strtoupper($selected->environment) : '—' }}</dd></div>
    @endif
    <div><dt class="text-slate-500">Beneficiary</dt><dd>{{ $selected->beneficiary_name }}</dd></div>
    <div><dt class="text-slate-500">Bank</dt><dd>{{ $selected->bank_name }}</dd></div>
    <div><dt class="text-slate-500">Account</dt><dd>{{ $selected->maskedAccount() }}</dd></div>
    <div><dt class="text-slate-500">IFSC</dt><dd>{{ $selected->ifsc_code }}</dd></div>
    <div><dt class="text-slate-500">Phone</dt><dd>{{ $selected->maskedPhone() }}</dd></div>
    <div><dt class="text-slate-500">Email</dt><dd>{{ $selected->maskedEmail() }}</dd></div>
    <div><dt class="text-slate-500">Mode</dt><dd>{{ strtoupper($selected->transfer_mode) }}</dd></div>
    <div><dt class="text-slate-500">Payout status</dt><dd class="font-medium">{{ $selected->status }}</dd></div>
    <div><dt class="text-slate-500">Wallet status</dt><dd>{{ $selected->wallet_state }}</dd></div>
    <div><dt class="text-slate-500">UTR</dt><dd>{{ $selected->utr_number ?: '—' }}</dd></div>
    <div class="sm:col-span-2"><dt class="text-slate-500">Failure reason</dt><dd>{{ $selected->failure_reason ?: '—' }}</dd></div>
    <div><dt class="text-slate-500">Created</dt><dd>{{ $selected->created_at?->format('d M Y H:i:s') }}</dd></div>
    <div><dt class="text-slate-500">Updated</dt><dd>{{ $selected->updated_at?->format('d M Y H:i:s') }}</dd></div>
    <div><dt class="text-slate-500">Processed</dt><dd>{{ $processed?->format('d M Y H:i:s') ?: '—' }}</dd></div>
    <div><dt class="text-slate-500">Refunded</dt><dd>{{ $selected->refunded_at?->format('d M Y H:i:s') ?: '—' }}</dd></div>
</dl>
<div class="mt-4">
    <h3 class="text-sm font-semibold text-slate-900">Status timeline</h3>
    <ol class="mt-2 space-y-2">
        @forelse($timeline as $event)
            <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                <span class="font-medium">{{ $event->status }}</span>
                <span class="text-slate-500">· wallet {{ $event->wallet_state }}</span>
                @if($event->utr_number)<span class="text-slate-700">· UTR {{ $event->utr_number }}</span>@endif
                <div class="text-xs text-slate-500">{{ $event->created_at?->format('d M Y H:i:s') }} · {{ $event->source }}</div>
                @if($event->failure_reason)<div class="text-xs text-rose-700">{{ $event->failure_reason }}</div>@endif
            </li>
        @empty
            <li class="text-sm text-slate-500">No status events recorded.</li>
        @endforelse
    </ol>
</div>
<div class="mt-4">
    <h3 class="text-sm font-semibold text-slate-900">Wallet ledger</h3>
    <ul class="mt-2 space-y-2">
        @forelse($ledger as $entry)
            <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                {{ strtoupper($entry->type) }} ₹{{ number_format((float) $entry->amount, 2) }}
                · reference {{ $entry->reference }}
                <div class="text-xs text-slate-500">{{ $entry->description }} · balance ₹{{ number_format((float) $entry->balance_after, 2) }}</div>
            </li>
        @empty
            <li class="text-sm text-slate-500">No ledger entries for this reference.</li>
        @endforelse
    </ul>
</div>
