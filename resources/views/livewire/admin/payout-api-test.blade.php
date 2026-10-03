<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Payout API</h1>
        <p class="mt-1 text-sm text-slate-500">Pre-delivery check for a vendor’s payout API. Select the vendor first. Each step stays locked until the previous step passes.</p>
    </div>

    <section class="fi-card mb-5 p-5 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Select Vendor</h2>
                <p class="mt-1 text-sm text-slate-500">Choose an existing active vendor. Tests use that vendor’s current payout access and API credential.</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,16rem)_minmax(0,1fr)]">
            <input wire:model.live.debounce.300ms="vendorSearch" type="search" autocomplete="off" class="fi-input text-sm" placeholder="Search name or vendor code">
            <select wire:model.live="vendorId" class="fi-input text-sm">
                <option value="">Select an active vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->business_name }} · {{ $vendor->vendor_code }}</option>
                @endforeach
            </select>
        </div>

        @if($selected)
            <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Vendor name</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selected['name'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Vendor code</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selected['code'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout API access</dt>
                    <dd class="mt-1 text-sm font-semibold {{ $selected['payout_access'] === 'Enabled' ? 'text-emerald-700' : 'text-red-700' }}">{{ $selected['payout_access'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">API credential status</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selected['credential_status'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                </div>
            </dl>
            @if($blockReason)
                <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $blockReason }}</p>
            @endif
        @else
            <p class="mt-4 text-sm text-slate-500">No vendor selected. API tests stay disabled until you choose one.</p>
        @endif
    </section>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[280px_minmax(0,1fr)]">
        <nav class="fi-card h-fit p-3" aria-label="Payout API steps">
            <ol class="space-y-1">
                @foreach($steps as $index => $item)
                    @php($number = $index + 1)
                    @php($locked = ! $this->stepUnlocked($number))
                    @php($status = $this->stepStatus($number))
                    <li>
                        <button type="button" wire:click="go({{ $number }})" @disabled($locked) class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left disabled:cursor-not-allowed disabled:opacity-45 {{ $step === $number ? 'bg-blue-50 text-blue-800' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $step === $number ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $number }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold">{{ $item['title'] }}</span>
                                <span class="block text-[11px] font-semibold uppercase tracking-wide {{ $status === 'PASS' ? 'text-emerald-700' : ($status === 'FAILED' ? 'text-red-700' : ($status === 'PENDING' ? 'text-amber-700' : 'text-slate-400')) }}">{{ $status }}</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ol>
        </nav>

        <section class="fi-card flex min-h-[420px] flex-col p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Step {{ $step }} of {{ $total }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $current['title'] }}</h2>
                </div>
                @php($status = $this->stepStatus($step))
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $status === 'PASS' ? 'bg-emerald-100 text-emerald-800' : ($status === 'FAILED' ? 'bg-red-100 text-red-700' : ($status === 'PENDING' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600')) }}">{{ $status }}</span>
            </div>

            <p class="mt-3 text-sm font-medium text-slate-700">
                @if($selected)
                    Selected vendor: {{ $selected['name'] }} · {{ $selected['code'] }}
                @else
                    Selected vendor: none
                @endif
            </p>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $current['purpose'] }}</p>

            @if($step === 5)
                <div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950">
                    This test will initiate a real VimoPay UAT payout. The vendor wallet is debited for the payout amount plus the FinPe commission. Nothing is sent until you confirm.
                </div>
            @endif

            @if($step === 1)
                <div class="mt-5">
                    <button type="button" wire:click="testAuthorization" wire:loading.attr="disabled" wire:target="testAuthorization" class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="testAuthorization">Test Authorization</span>
                        <span wire:loading wire:target="testAuthorization">Testing…</span>
                    </button>
                </div>

                @if($authResult)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Result</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $authResult['success'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $authResult['success'] ? 'PASS' : 'FAILED' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $authResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $authResult['message'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Authorization token received</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $authResult['token_received'] ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response time</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $authResult['response_ms'] !== null ? $authResult['response_ms'].' ms' : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                        </div>
                    </dl>
                @endif
            @elseif($step === 2)
                <div class="mt-5">
                    <button type="button" wire:click="testBankList" wire:loading.attr="disabled" wire:target="testBankList" class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="testBankList">Test Bank List</span>
                        <span wire:loading wire:target="testBankList">Testing…</span>
                    </button>
                </div>

                @if($bankResult)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Result</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $bankResult['success'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $bankResult['success'] ? 'PASS' : 'FAILED' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $bankResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $bankResult['message'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total banks returned</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $bankResult['total'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response time</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $bankResult['response_ms'] !== null ? $bankResult['response_ms'].' ms' : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                        </div>
                    </dl>

                    @if($bankResult['banks'] !== [])
                        <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">Bank code</th>
                                        <th class="px-4 py-3">Bank description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($bankResult['banks'] as $bank)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-900">{{ $bank['code'] !== '' ? $bank['code'] : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $bank['description'] !== '' ? $bank['description'] : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            @elseif($step === 3)
                <div class="mt-5">
                    <button type="button" wire:click="testPurposeList" wire:loading.attr="disabled" wire:target="testPurposeList" class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="testPurposeList">Test Purpose of Payout</span>
                        <span wire:loading wire:target="testPurposeList">Testing…</span>
                    </button>
                </div>

                @if($purposeResult)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Result</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $purposeResult['success'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $purposeResult['success'] ? 'PASS' : 'FAILED' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $purposeResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $purposeResult['message'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total purposes returned</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $purposeResult['total'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response time</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $purposeResult['response_ms'] !== null ? $purposeResult['response_ms'].' ms' : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                        </div>
                    </dl>

                    @if($purposeResult['purposes'] !== [])
                        <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">Purpose code</th>
                                        <th class="px-4 py-3">Purpose description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($purposeResult['purposes'] as $purpose)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-900">{{ $purpose['code'] !== '' ? $purpose['code'] : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $purpose['description'] !== '' ? $purpose['description'] : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            @elseif($step === 4)
                <div class="mt-5">
                    <button type="button" wire:click="testStateList" wire:loading.attr="disabled" wire:target="testStateList" class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="testStateList">Test State List</span>
                        <span wire:loading wire:target="testStateList">Testing…</span>
                    </button>
                </div>

                @if($stateResult)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Result</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $stateResult['success'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $stateResult['success'] ? 'PASS' : 'FAILED' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $stateResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $stateResult['message'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total states returned</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $stateResult['total'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response time</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $stateResult['response_ms'] !== null ? $stateResult['response_ms'].' ms' : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                        </div>
                    </dl>

                    @if($stateResult['states'] !== [])
                        <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">State code</th>
                                        <th class="px-4 py-3">State description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($stateResult['states'] as $state)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-900">{{ $state['code'] !== '' ? $state['code'] : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $state['description'] !== '' ? $state['description'] : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            @elseif($step === 5)
                <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Vendor</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selected ? $selected['name'].' · '.$selected['code'] : 'None' }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout commission / charge</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutEstimate['rule'] ?? '—' }}{{ isset($payoutEstimate['charge']) && $payoutEstimate['charge'] !== null ? ' · ₹'.number_format($payoutEstimate['charge'], 2) : '' }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estimated wallet debit</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ isset($payoutEstimate['total']) && $payoutEstimate['total'] !== null ? '₹'.number_format($payoutEstimate['total'], 2) : 'Enter an amount' }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Wallet after estimate</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ isset($payoutEstimate['wallet_after']) && $payoutEstimate['wallet_after'] !== null ? '₹'.number_format($payoutEstimate['wallet_after'], 2) : '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">Beneficiary name<input wire:model="beneficiaryName" type="text" maxlength="120" class="fi-input mt-1 w-full text-sm" autocomplete="off"></label>
                    <label class="text-sm font-medium text-slate-700">Beneficiary account number<input wire:model="beneficiaryAccount" type="text" maxlength="18" inputmode="numeric" class="fi-input mt-1 w-full text-sm" autocomplete="off"></label>
                    <label class="text-sm font-medium text-slate-700">Beneficiary IFSC<input wire:model="beneficiaryIfsc" type="text" maxlength="11" class="fi-input mt-1 w-full text-sm uppercase" autocomplete="off"></label>
                    <label class="text-sm font-medium text-slate-700">Beneficiary bank code
                        <select wire:model.live="beneficiaryBank" class="fi-input mt-1 w-full text-sm">
                            <option value="">Select from Step 2 bank list</option>
                            @foreach($payoutBanks as $bank)
                                <option value="{{ $bank['code'] }}">{{ $bank['code'] }}{{ $bank['description'] !== '' ? ' · '.$bank['description'] : '' }}</option>
                            @endforeach
                        </select>
                        @if(is_array($selectedBank))
                            <span class="mt-1 block text-xs font-medium text-slate-600">{{ $selectedBank['code'] }} · {{ $selectedBank['description'] !== '' ? $selectedBank['description'] : '—' }}</span>
                        @endif
                    </label>
                    <label class="text-sm font-medium text-slate-700">Beneficiary mobile<input wire:model="beneficiaryMobile" type="text" maxlength="10" inputmode="numeric" class="fi-input mt-1 w-full text-sm" autocomplete="off"></label>
                    <label class="text-sm font-medium text-slate-700">State
                        <select wire:model="beneficiaryState" class="fi-input mt-1 w-full text-sm">
                            <option value="">Select from state list</option>
                            @foreach($payoutStates as $state)
                                <option value="{{ $state['code'] }}">{{ $state['code'] }}{{ $state['description'] !== '' ? ' · '.$state['description'] : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-medium text-slate-700">Payment purpose
                        <select wire:model="paymentPurpose" class="fi-input mt-1 w-full text-sm">
                            <option value="">Select from purpose list</option>
                            @foreach($payoutPurposes as $purpose)
                                <option value="{{ $purpose['code'] }}">{{ $purpose['code'] }}{{ $purpose['description'] !== '' ? ' · '.$purpose['description'] : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-medium text-slate-700">Payment mode
                        <select wire:model="paymentMode" class="fi-input mt-1 w-full text-sm">
                            <option value="IMPS">IMPS</option>
                            <option value="NEFT">NEFT</option>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-slate-700">Amount<input wire:model.live.debounce.400ms="amount" type="number" min="100" max="99999.99" step="0.01" class="fi-input mt-1 w-full text-sm"></label>
                    <label class="text-sm font-medium text-slate-700">Merchant reference ID<input wire:model="merchantRefId" type="text" maxlength="40" class="fi-input mt-1 w-full text-sm" placeholder="Generated if left blank" autocomplete="off"></label>
                    <label class="text-sm font-medium text-slate-700">Latitude<input wire:model="payoutLat" type="text" maxlength="30" class="fi-input mt-1 w-full text-sm" autocomplete="off"></label>
                    <label class="text-sm font-medium text-slate-700">Longitude<input wire:model="payoutLong" type="text" maxlength="30" class="fi-input mt-1 w-full text-sm" autocomplete="off"></label>
                </div>
                <p class="mt-2 text-xs text-slate-500">Account number must be 9 to 18 digits. IFSC must be 11 characters. Mobile must be 10 digits. Latitude must be from -90 to 90 and longitude from -180 to 180. Bank, purpose, and state codes come from the tested lists.</p>

                <div class="mt-5">
                    <button type="button" wire:click="reviewPayout" wire:loading.attr="disabled" wire:target="reviewPayout,confirmPayout" class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60">
                        Test Payout
                    </button>
                </div>

                @if($payoutConfirming)
                    <div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-4 text-sm text-amber-950">
                        <p class="font-semibold">This test will initiate a real VimoPay UAT payout.</p>
                        <p class="mt-2">Vendor {{ data_get($selected, 'name', '—') }} · {{ $providerName }} · {{ $environment }}. Amount ₹{{ is_numeric($amount) ? number_format((float) $amount, 2) : '—' }}, commission ₹{{ isset($payoutEstimate['charge']) && $payoutEstimate['charge'] !== null ? number_format($payoutEstimate['charge'], 2) : '—' }}, estimated debit ₹{{ isset($payoutEstimate['total']) && $payoutEstimate['total'] !== null ? number_format($payoutEstimate['total'], 2) : '—' }}. Merchant reference {{ $merchantRefId !== '' ? $merchantRefId : '—' }}.</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="button" wire:click="confirmPayout" wire:loading.attr="disabled" wire:target="confirmPayout" class="inline-flex rounded-lg bg-amber-700 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-800 disabled:cursor-wait disabled:opacity-60">Confirm and send</button>
                            <button type="button" wire:click="cancelPayoutReview" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        </div>
                    </div>
                @endif

                @if($payoutResult)
                    @php($payoutLabel = $payoutResult['outcome'] === 'pass' ? 'PASS' : ($payoutResult['outcome'] === 'pending' ? 'PENDING' : 'FAILED'))
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Result</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $payoutResult['outcome'] === 'pass' ? 'text-emerald-700' : ($payoutResult['outcome'] === 'pending' ? 'text-amber-700' : 'text-red-700') }}">{{ $payoutLabel }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider status / message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $payoutResult['provider_status'] ? ucfirst($payoutResult['provider_status']).' · ' : '' }}{{ $payoutResult['message'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">FinPe reference</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['reference'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Merchant reference</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['merchant_ref'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider reference</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['provider_reference'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Beneficiary</dt>
                            <dd class="mt-1 text-sm text-slate-800">Account {{ $payoutResult['account_mask'] }} · Mobile {{ $payoutResult['mobile_mask'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout amount</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['amount'] !== null ? '₹'.number_format($payoutResult['amount'], 2) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Commission / charge</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['charge'] !== null ? '₹'.number_format($payoutResult['charge'], 2) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total debited</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['total_debited'] !== null ? '₹'.number_format($payoutResult['total_debited'], 2) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Wallet before / after</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['wallet_before'] !== null ? '₹'.number_format($payoutResult['wallet_before'], 2) : '—' }} / {{ $payoutResult['wallet_after'] !== null ? '₹'.number_format($payoutResult['wallet_after'], 2) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Response time</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutResult['response_ms'] !== null ? $payoutResult['response_ms'].' ms' : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                        </div>
                    </dl>
                @endif
            @elseif($step === 6)
                @if($payoutSnapshot)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">FinPe transaction reference</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutSnapshot['reference'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Merchant reference</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutSnapshot['merchant_ref'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">VimoPay provider reference</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutSnapshot['provider_reference'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider RRN</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutSnapshot['rrn'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Current transaction status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutSnapshot['status'] ? ucfirst($payoutSnapshot['status']) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider status / message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $payoutSnapshot['message'] ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout amount</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">₹{{ number_format($payoutSnapshot['amount'], 2) }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Commission / charge</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">₹{{ number_format($payoutSnapshot['charge'], 2) }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total debited</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">₹{{ number_format($payoutSnapshot['total'], 2) }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout provider</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payoutSnapshot['provider'] ?: $providerName }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-5 text-sm text-slate-500">The Step 5 payout reference is not available for this vendor.</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button" wire:click="checkTransactionStatus" wire:loading.attr="disabled" wire:target="checkTransactionStatus,testCallback" class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-wait disabled:opacity-60">Check Transaction Status</button>
                    <button type="button" wire:click="testCallback" wire:loading.attr="disabled" wire:target="checkTransactionStatus,testCallback" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60">Test Callback</button>
                </div>

                @if($statusCheckResult)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status check</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $statusCheckResult['success'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $statusCheckResult['success'] ? 'PASS' : 'FAILED' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $statusCheckResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Final mapped status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $statusCheckResult['mapped_status'] ? ucfirst($statusCheckResult['mapped_status']) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider status / message</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $statusCheckResult['message'] ?: '—' }}</dd>
                        </div>
                    </dl>
                @endif

                @if($callbackCheckResult)
                    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Callback</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $callbackCheckResult['success'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $callbackCheckResult['success'] ? 'PASS' : 'FAILED' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $callbackCheckResult['http_status'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Callback acknowledgement</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $callbackCheckResult['acknowledgement'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Resulting transaction status</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $callbackCheckResult['status'] ? ucfirst($callbackCheckResult['status']) : '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Wallet effect</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $callbackCheckResult['wallet_effect'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Commission entry effect</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $callbackCheckResult['commission_effect'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3 sm:col-span-2">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Duplicate / idempotency</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $callbackCheckResult['idempotency'] }}</dd>
                        </div>
                    </dl>
                @endif
            @elseif($step === 7)
                @if($testReport && $testReport['passed'])
                    <div class="mt-5 rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-5">
                        <p class="text-lg font-bold tracking-wide text-emerald-900">PAYMENT API UAT TEST PASSED</p>
                        <p class="mt-2 text-sm font-medium text-emerald-800">Vendor is ready for Payout API integration testing.</p>
                    </div>
                @else
                    <p class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Steps 1 through 6 must pass before this report can be completed.</p>
                @endif

                <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Vendor</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selected ? $selected['name'].' · '.$selected['code'] : 'None' }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payout API access</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ data_get($selected, 'payout_access', '—') }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">API credential</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ data_get($selected, 'credential_status', '—') }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Provider</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $providerName }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Environment</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $environment }}</dd>
                    </div>
                </dl>

                @if($testReport)
                    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Step</th>
                                    <th class="px-4 py-3">Result</th>
                                    <th class="px-4 py-3">Summary</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($testReport['steps'] as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $row['title'] }}</td>
                                        <td class="px-4 py-3 font-semibold {{ $row['status'] === 'PASS' ? 'text-emerald-700' : ($row['status'] === 'FAILED' ? 'text-red-700' : 'text-slate-500') }}">{{ $row['status'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['summary'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($testReport['payout'])
                        <h3 class="mt-6 text-sm font-bold uppercase tracking-wider text-slate-500">Successful payout</h3>
                        <dl class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach([
                                'FinPe reference' => $testReport['payout']['reference'],
                                'Merchant reference' => $testReport['payout']['merchant_ref'],
                                'VimoPay provider reference' => $testReport['payout']['provider_reference'],
                                'RRN' => $testReport['payout']['rrn'],
                                'Payout amount' => $testReport['payout']['amount'],
                                'Commission / charge' => $testReport['payout']['charge'],
                                'Total debited' => $testReport['payout']['total'],
                                'Final status' => $testReport['payout']['status'],
                                'Wallet debit' => $testReport['payout']['wallet_debit'],
                                'Commission entry' => $testReport['payout']['commission'],
                            ] as $label => $value)
                                <div class="rounded-xl border border-slate-200 px-4 py-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                @endif
            @endif

            <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                <button type="button" wire:click="previous" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40" @disabled($step === 1)>Previous</button>
                <button type="button" wire:click="next" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-40" @disabled($step === $total || ! $this->stepUnlocked($step + 1))>Next</button>
            </div>
        </section>
    </div>
</div>
