<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Send Money</h1>
            <p class="mt-1 text-sm text-slate-500">Transfer funds via IMPS / NEFT / RTGS.</p>
        </div>
    </div>

    {{-- Step bar --}}
    <div class="fi-card mb-6 overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-2">
                @foreach(['form'=>'1. Details','confirm'=>'2. Confirm','success'=>'3. Done'] as $s => $lbl)
                    @php
                        $done = ($s === 'form' && in_array($step,['confirm','success']))
                             || ($s === 'confirm' && $step === 'success');
                        $curr = $step === $s;
                    @endphp
                    <div class="flex items-center">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full border text-sm font-semibold
                            {{ $curr ? 'border-violet-600 bg-violet-600 text-white' : ($done ? 'border-green-300 bg-green-100 text-green-700' : 'border-slate-300 bg-white text-slate-400') }}">
                            {{ $done ? '✓' : $loop->index + 1 }}
                        </span>
                        <span class="ml-2 hidden text-sm font-medium lg:block
                            {{ $curr ? 'text-violet-700' : ($done ? 'text-green-700' : 'text-slate-400') }}">
                            {{ explode('. ',$lbl)[1] }}
                        </span>
                        @if(!$loop->last)<div class="mx-3 h-px w-8 bg-slate-200 lg:w-16"></div>@endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- STEP 1: Form --}}
    @if($step === 'form')
    <div class="fi-card p-6">
        <h3 class="mb-5 text-base font-semibold text-slate-900">Transfer Details</h3>

        {{-- Saved beneficiary --}}
        @if($this->beneficiaries->isNotEmpty())
        <div class="mb-5">
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Load from Saved Beneficiary</label>
            <select wire:model.live="beneficiaryId" wire:change="fillBeneficiary" class="fi-input text-sm">
                <option value="">— Enter manually —</option>
                @foreach($this->beneficiaries as $b)
                    <option value="{{ $b->id }}">{{ $b->name }} — {{ $b->account_number }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Beneficiary Name *</label>
                <input wire:model="beneficiaryName" type="text" class="fi-input text-sm" placeholder="Full name">
                @error('beneficiaryName')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Account Number *</label>
                <input wire:model="accountNumber" type="text" class="fi-input text-sm" placeholder="Bank account number">
                @error('accountNumber')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">IFSC Code</label>
                <input wire:model="ifscCode" type="text" class="fi-input text-sm" placeholder="SBIN0001234">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Bank Name</label>
                <input wire:model="bankName" type="text" class="fi-input text-sm" placeholder="State Bank of India">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Amount (₹) *</label>
                <input wire:model="amount" type="number" min="1" step="0.01" class="fi-input text-sm" placeholder="0.00">
                @error('amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Remarks</label>
                <input wire:model="remarks" type="text" class="fi-input text-sm" placeholder="Optional note">
            </div>
        </div>

        @if(!$beneficiaryId)
        <label class="mt-4 flex items-center gap-2 text-sm text-slate-600">
            <input wire:model="saveBeneficiary" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-violet-600">
            Save as beneficiary for future transfers
        </label>
        @endif

        <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
            <button wire:click="preview"
                    class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                Preview Transfer
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 2: Confirm --}}
    @if($step === 'confirm' && $previewData)
    <div class="fi-card p-6">
        <h3 class="mb-5 text-base font-semibold text-slate-900">Confirm Transfer</h3>
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
            <div class="space-y-4">
                @foreach([
                    ['Beneficiary Name', $previewData['beneficiary_name']],
                    ['Account Number',   $previewData['account_number']],
                    ['IFSC Code',        $previewData['ifsc_code'] ?: '—'],
                    ['Bank',             $previewData['bank_name'] ?: '—'],
                    ['Remarks',          $previewData['remarks']   ?: '—'],
                ] as [$l,$v])
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm text-slate-500">{{ $l }}</span>
                        <span class="text-sm font-semibold text-slate-800">{{ $v }}</span>
                    </div>
                @endforeach
                <div class="flex items-center justify-between pt-1">
                    <span class="text-base font-bold text-slate-900">Transfer Amount</span>
                    <span class="text-2xl font-bold text-violet-700">₹{{ $previewData['amount'] }}</span>
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button wire:click="$set('step','form')"
                    class="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                ← Edit Details
            </button>
            <button wire:click="submit"
                    class="flex-1 rounded-xl bg-violet-600 py-3 text-sm font-semibold text-white hover:bg-violet-700">
                Confirm & Send Money
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 3: Success --}}
    @if($step === 'success')
    <div class="fi-card p-12 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
            <svg class="h-10 w-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="mt-5 text-2xl font-bold text-slate-900">Transfer Initiated!</h2>
        <p class="mt-2 text-sm text-slate-500">Your transaction is being processed by the bank.</p>
        <div class="mx-auto mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2">
            <span class="text-xs text-slate-500">Reference:</span>
            <span class="font-mono text-sm font-bold text-slate-800">{{ $txReference }}</span>
        </div>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ route('vendor.transactions') }}"
               class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                View Transactions
            </a>
            <button wire:click="newTransaction"
                    class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                New Transfer
            </button>
        </div>
    </div>
    @endif
</div>
