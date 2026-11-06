<div class="mx-auto max-w-6xl space-y-5">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">AlliancePe payout</h1>
        <p class="text-sm text-slate-500">Available balance ₹{{ number_format($balance, 2) }}</p>
    </div>
    @if($notice)<div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $notice }}</div>@endif
    @if($error && ! $balanceAlert)<div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $error }}</div>@endif

    <form wire:submit="review" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2">
        <label class="text-sm">Amount<input wire:model="amount" type="number" min="100" max="100000" step="0.01" class="fi-input mt-1 w-full"></label>
        <label class="text-sm">Transfer mode
            <select wire:model="transferMode" class="fi-input mt-1 w-full"><option>IMPS</option><option>NEFT</option><option>RTGS</option></select>
        </label>
        <label class="text-sm">Beneficiary name<input wire:model="beneficiaryName" class="fi-input mt-1 w-full" maxlength="100"></label>
        <label class="text-sm">Bank name<input wire:model="bankName" class="fi-input mt-1 w-full"></label>
        <label class="text-sm">Account number<input wire:model="accountNumber" class="fi-input mt-1 w-full" inputmode="numeric"></label>
        <label class="text-sm">Confirm account number<input wire:model="confirmAccountNumber" class="fi-input mt-1 w-full" inputmode="numeric"></label>
        <label class="text-sm">IFSC<input wire:model="ifsc" class="fi-input mt-1 w-full uppercase" maxlength="11"></label>
        <label class="text-sm">Phone<input wire:model="phone" class="fi-input mt-1 w-full" maxlength="10"></label>
        <label class="text-sm sm:col-span-2">Email<input wire:model="email" type="email" class="fi-input mt-1 w-full"></label>
        <label class="text-sm sm:col-span-2">Remarks<input wire:model="remarks" class="fi-input mt-1 w-full" maxlength="255"></label>
        <div class="sm:col-span-2">
            <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">Review payout</button>
        </div>
    </form>

    @if($confirming)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-slate-900">
            @if($balanceAlert)
                <p class="font-semibold leading-6" role="alert">{{ $balanceAlert }}</p>
            @else
                <p class="font-semibold leading-6">Confirm payout of ₹{{ number_format((float) $amount, 2) }} to {{ $beneficiaryName }}.</p>
            @endif
            <p class="mt-2 leading-6 text-slate-800">Reference {{ $merchantOrderId }}. Account ending {{ substr(preg_replace('/\D/', '', $accountNumber), -4) }}.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="submit">Submit</span>
                    <span wire:loading.inline-flex wire:target="submit" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
                <button type="button" wire:click="cancelReview" wire:loading.attr="disabled" wire:target="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 disabled:cursor-not-allowed disabled:opacity-60">Cancel</button>
            </div>
        </div>
    @endif

    <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">My payout transactions</h2>
        <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-sm">Transaction reference<input wire:model.live.debounce.300ms="search" class="fi-input mt-1 w-full"></label>
            <label class="text-sm">Status
                <select wire:model.live="status" class="fi-input mt-1 w-full">
                    <option value="">All</option>
                    @foreach(['pending','processing','completed','failed','cancelled','on_hold','rejected'] as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm">From<input wire:model.live="dateFrom" type="date" class="fi-input mt-1 w-full"></label>
            <label class="text-sm">To<input wire:model.live="dateTo" type="date" class="fi-input mt-1 w-full"></label>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Reference</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2">FinPe commission</th>
                        <th class="px-3 py-2">Total wallet debit</th>
                        <th class="px-3 py-2">Beneficiary</th>
                        <th class="px-3 py-2">Account</th>
                        <th class="px-3 py-2">Bank</th>
                        <th class="px-3 py-2">Mode</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">UTR</th>
                        <th class="px-3 py-2">Failure</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payouts as $row)
                    <tr wire:click="show({{ $row->id }})" class="cursor-pointer border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-3 py-2 font-medium">{{ $row->merchant_order_id }}</td>
                        <td class="px-3 py-2">{{ $row->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2">₹{{ number_format((float) $row->amount, 2) }}</td>
                        <td class="px-3 py-2">{{ $row->commission_type ? $row->commission_type.' '.$row->commission_rate : '—' }} · ₹{{ number_format((float) $row->commission_amount, 2) }}</td>
                        <td class="px-3 py-2">₹{{ number_format((float) ($row->total_debited ?? $row->amount), 2) }}</td>
                        <td class="px-3 py-2">{{ $row->beneficiary_name }}</td>
                        <td class="px-3 py-2">{{ $row->maskedAccount() }}</td>
                        <td class="px-3 py-2">{{ $row->bank_name }}</td>
                        <td class="px-3 py-2">{{ strtoupper($row->transfer_mode) }}</td>
                        <td class="px-3 py-2">{{ $row->status }}</td>
                        <td class="px-3 py-2">{{ $row->utr_number ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $row->failure_reason ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="px-3 py-6 text-center text-slate-500">No payouts match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $payouts->links() }}</div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        @if($selected)
            <h2 class="mb-3 font-semibold text-slate-900">Transaction detail</h2>
            @include('livewire.alliancepe.payout-detail', ['showVendor' => false])
        @else
            <p class="text-sm text-slate-500">Select one of your payouts to see status, UTR, and the wallet reference.</p>
        @endif
    </section>
</div>
