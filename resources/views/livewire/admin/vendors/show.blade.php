<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $vendor->business_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $vendor->vendor_code }} · {{ $vendor->pmt_code }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if((int) $vendor->registration_step < 7)
                <a href="{{ route('admin.vendors.create', $vendor) }}" class="fi-btn fi-btn-secondary">
                    Continue Registration
                </a>
            @endif
            <a href="{{ route('admin.vendors') }}" class="fi-btn fi-btn-secondary">
                ← Back
            </a>
        </div>
    </div>

    @php
        $statusCls = match($vendor->status) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'suspended' => 'bg-red-50 text-red-600 border-red-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };
        $kycCls = match($vendor->kyc_status) {
            'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-red-50 text-red-600 border-red-200',
            'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
        $kycLabel = match($vendor->kyc_status) {
            'verified' => 'KYC Approved',
            'rejected' => 'Rejected',
            'submitted' => 'Submitted',
            default => 'Pending',
        };
    @endphp

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">Status</p>
            <span class="mt-2 inline-block rounded-full border px-3 py-1 text-sm font-bold {{ $statusCls }}">{{ ucfirst($vendor->status) }}</span>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">KYC</p>
            <span class="mt-2 inline-block rounded-full border px-3 py-1 text-sm font-bold {{ $kycCls }}">{{ $kycLabel }}</span>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">Wallet</p>
            <p class="mt-2 text-lg font-bold text-slate-900">₹{{ number_format((float) ($vendor->wallet->balance ?? 0), 2) }}</p>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">API</p>
            <p class="mt-2 text-sm font-bold {{ $vendor->api_enabled ? 'text-emerald-600' : 'text-slate-400' }}">{{ $vendor->api_enabled ? 'Enabled' : 'Disabled' }}</p>
        </div>
    </div>

    @if($reviewMessage)
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $reviewMessage }}
        </div>
    @endif

    <div class="mb-5 flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-1">
        @foreach([
            'kyc' => 'KYC',
            'profile' => 'Profile',
            'wallet' => 'Wallet',
            'transactions' => 'Transactions',
            'settlements' => 'Settlements',
            'beneficiaries' => 'Beneficiaries',
            'developer' => 'API / Developer',
        ] as $t => $lbl)
            <button type="button" wire:click="setTab('{{ $t }}')"
                    class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition
                           {{ $tab === $t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $lbl }}
            </button>
        @endforeach
    </div>

    @php
        $kv = fn ($label, $value) => [
            'label' => $label,
            'value' => ($value === null || $value === '') ? '—' : $value,
        ];
    @endphp

    {{-- KYC --}}
    @if($tab === 'kyc')
        <div class="space-y-5">
            <div class="fi-card p-6">
                <h3 class="text-base font-semibold text-slate-900">KYC Verification</h3>
                <p class="mt-1 text-sm text-slate-500">
                    @if($vendor->kyc_status === 'submitted')
                        Review all submitted steps, add a comment, then approve or reject.
                    @elseif($vendor->kyc_status === 'rejected')
                        KYC rejected. Approve / Reject buttons will appear again after the vendor resubmits.
                    @elseif($vendor->kyc_status === 'verified')
                        KYC is approved. Previous review comments are listed below.
                    @else
                        Waiting for the vendor to submit KYC.
                    @endif
                </p>

                @if($vendor->kyc_status === 'submitted')
                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Comment</label>
                        <textarea wire:model="kycComment" rows="3" class="fi-input" placeholder="Add review comment for the vendor"></textarea>
                        @error('kycComment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        @if(auth('admin')->user()->hasPermission('vendors', 'approve'))
                        <button type="button" wire:click="approveKyc" class="fi-btn fi-btn-success">
                            Approve KYC
                        </button>
                        @endif
                        @if(auth('admin')->user()->hasPermission('vendors', 'reject'))
                        <button type="button" wire:click="rejectKyc" class="fi-btn fi-btn-danger">
                            Reject KYC
                        </button>
                        @endif
                    </div>
                @endif

                <div class="mt-5 space-y-3">
                    <h4 class="text-sm font-semibold text-slate-800">Review comments</h4>
                    @forelse($vendor->kycReviews as $review)
                        @php
                            $reviewCls = match($review->action) {
                                'approved' => 'border-emerald-200 bg-emerald-50',
                                'rejected' => 'border-red-200 bg-red-50',
                                default => 'border-slate-200 bg-slate-50',
                            };
                            $reviewLabel = match($review->action) {
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'submitted' => 'Vendor resubmitted',
                                default => ucfirst($review->action),
                            };
                        @endphp
                        <div class="rounded-xl border px-4 py-3 {{ $reviewCls }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-slate-700">{{ $reviewLabel }}</p>
                                <p class="text-xs text-slate-500">{{ $review->created_at->format('d M Y H:i') }}</p>
                            </div>
                            @if($review->comment)
                                <p class="mt-1.5 text-sm text-slate-800">{{ $review->comment }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-500">
                                @if($review->admin)
                                    by {{ $review->admin->name }}
                                @else
                                    by Vendor
                                @endif
                            </p>
                        </div>
                    @empty
                        @if($vendor->kyc_comment)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-sm text-slate-800">{{ $vendor->kyc_comment }}</p>
                                @if($vendor->kyc_reviewed_at)
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $vendor->kyc_reviewed_at->format('d M Y H:i') }}
                                        @if($vendor->kycReviewer) · {{ $vendor->kycReviewer->name }} @endif
                                    </p>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-slate-400">No review comments yet.</p>
                        @endif
                    @endforelse
                </div>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">1. Registration</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach([
                        $kv('Business Name', $vendor->business_name),
                        $kv('Contact Person', $vendor->contact_name),
                        $kv('Email', $vendor->email),
                        $kv('Phone', $vendor->phone),
                        $kv('Country', $vendor->country),
                        $kv('Address', $vendor->address),
                    ] as $item)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400">{{ $item['label'] }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">2. Legal Details</h3>
                @if($vendor->legalDetails)
                    @php $ld = $vendor->legalDetails; @endphp
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach([
                            $kv('Entity Type', $ld->entity_type),
                            $kv('Registered With', $ld->registration_body),
                            $kv('Corporate Identification Number (CIN)', $ld->registration_number),
                            $kv('PAN Card Number', $ld->tax_identification),
                            $kv('RBI Regulated', $ld->rbi_regulated ? 'Yes' : 'No'),
                            $kv('Incorporation Year', $ld->incorporation_year),
                            $kv('Merchant Acquiring Years', $ld->merchant_acquiring_years),
                            $kv('Additional Licenses', $ld->additional_licenses),
                        ] as $item)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400">{{ $item['label'] }}</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">{{ $item['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">Not submitted.</p>
                @endif
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">3. Promoters / Shareholders</h3>
                @forelse($vendor->promoters as $i => $p)
                    <div class="mb-4 rounded-xl border border-slate-200 p-4 last:mb-0">
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Promoter {{ $i + 1 }}</p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @foreach([
                                $kv('Name', $p->full_name),
                                $kv('Share %', $p->shareholding_percentage),
                                $kv('PAN Card Number', $p->pan_card_no),
                                $kv('Aadhaar Card Number', $p->aadhaar_card_no),
                                $kv('DOB', optional($p->date_of_birth)->format('d M Y')),
                                $kv('Address', $p->official_address),
                            ] as $item)
                                <div>
                                    <p class="text-xs font-semibold text-slate-400">{{ $item['label'] }}</p>
                                    <p class="mt-0.5 text-sm font-medium text-slate-800">{{ $item['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Not submitted.</p>
                @endforelse
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">4. Directors / KMP & IT</h3>
                @forelse($vendor->directors as $i => $d)
                    <div class="mb-4 rounded-xl border border-slate-200 p-4 last:mb-0">
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Director {{ $i + 1 }}</p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @foreach([
                                $kv('Name', $d->name),
                                $kv('Designation', $d->designation),
                                $kv('PAN', $d->pan_card_no),
                                $kv('DOB', optional($d->date_of_birth)->format('d M Y')),
                                $kv('Address', $d->official_address),
                            ] as $item)
                                <div>
                                    <p class="text-xs font-semibold text-slate-400">{{ $item['label'] }}</p>
                                    <p class="mt-0.5 text-sm font-medium text-slate-800">{{ $item['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No directors submitted.</p>
                @endforelse

                @if($vendor->teamItDetails)
                    @php $ti = $vendor->teamItDetails; @endphp
                    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-5">
                        @foreach([['Total', $ti->total_employees], ['Tech', $ti->technology_employees], ['Sales', $ti->sales_employees], ['Support', $ti->support_employees], ['Admin/HR', $ti->admin_finance_hr_employees]] as [$l, $v])
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                                <p class="text-xl font-bold text-slate-900">{{ $v ?? 0 }}</p>
                                <p class="text-xs text-slate-400">{{ $l }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    @endif

    {{-- PROFILE --}}
    @if($tab === 'profile')
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Business Profile</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Business Name', $vendor->business_name],
                        ['Contact Person', $vendor->contact_name],
                        ['Email', $vendor->email],
                        ['Phone', $vendor->phone],
                        ['Country', $vendor->country],
                        ['Address', $vendor->address ?? '—'],
                        ['Vendor Code', $vendor->vendor_code],
                        ['PMT Code', $vendor->pmt_code ?? '—'],
                        ['Registered', $vendor->created_at->format('d M Y')],
                    ] as [$l, $v])
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-400">{{ $l }}</span>
                            <span class="text-right text-xs font-semibold text-slate-800">{{ $v }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Configured Commission Rule</h3>
                <p class="mb-3 text-xs text-slate-500">Active commission rule for this partner. This is not applied commission.</p>
                <div class="space-y-3">
                    @foreach([
                        ['Transaction Limit', '₹'.number_format((float) $vendor->transaction_limit, 2)],
                        ['Configured type', $configuredRule ? ($configuredRule->calc_type === 'percentage' ? 'Percentage' : 'Fixed') : '—'],
                        ['Configured rate', ! $configuredRule ? '—' : ($configuredRule->calc_type === 'percentage' ? number_format((float) $configuredRule->value, 2).'%' : '₹'.number_format((float) $configuredRule->value, 2))],
                        ['API Enabled', $vendor->api_enabled ? 'Yes' : 'No'],
                    ] as [$l, $v])
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400">{{ $l }}</span>
                            <span class="text-xs font-semibold text-slate-800">{{ $v }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        <div class="fi-card p-6 lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-900">Applied Commission</h3>
            <p class="mt-1 text-xs text-slate-500">Recorded commission entries for this partner.</p>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400">Commission entries</p>
                    <p class="text-xl font-bold text-slate-900">{{ $appliedCommission->entry_count ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Total commission</p>
                    <p class="text-xl font-bold text-slate-900">₹{{ number_format((float) ($appliedCommission->total_commission ?? 0), 2) }}</p>
                </div>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>@foreach(['Reference','Amount','Rate','Commission','Status','Date'] as $col)<th class="px-3 py-2 text-left text-[11px] font-semibold uppercase text-slate-400">{{ $col }}</th>@endforeach</tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentCommission as $entry)
                            <tr>
                                <td class="px-3 py-2">{{ $entry->source_reference ?: '—' }}</td>
                                <td class="px-3 py-2">₹{{ number_format((float) $entry->base_amount, 2) }}</td>
                                <td class="px-3 py-2">{{ $entry->calc_type === 'percentage' ? number_format((float) $entry->rate_value, 2).'%' : '₹'.number_format((float) $entry->rate_value, 2) }}</td>
                                <td class="px-3 py-2 font-semibold">₹{{ number_format((float) $entry->commission_amount, 2) }}</td>
                                <td class="px-3 py-2">{{ ucfirst((string) $entry->status) }}</td>
                                <td class="px-3 py-2 text-xs">{{ $entry->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-6 text-sm text-slate-500">No applied commission.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    @endif

    {{-- WALLET --}}
    @if($tab === 'wallet')
        <div class="space-y-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="fi-card p-5">
                    <p class="text-xs text-slate-400">Available Balance</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">₹{{ number_format((float) ($vendor->wallet->balance ?? 0), 2) }}</p>
                </div>
                <div class="fi-card p-5">
                    <p class="text-xs text-slate-400">Hold Balance</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">₹{{ number_format((float) ($vendor->wallet->hold_balance ?? 0), 2) }}</p>
                </div>
            </div>

            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">Ledger</h3>
                </div>
                @if($ledger && $ledger->isNotEmpty())
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Payout Provider</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($ledger as $row)
                                @php
                                    $payout = $row->type === 'debit' ? ($payoutByReference[$row->reference] ?? null) : null;
                                    $providerName = $payout ? \App\Support\CommissionProviders::name($payout->payout_provider) : null;
                                @endphp
                                <tr>
                                    <td class="px-5 py-3">{{ $row->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-5 py-3 text-xs font-semibold {{ $row->type === 'credit' ? 'text-emerald-700' : 'text-red-700' }}">{{ ucfirst($row->type) }}</td>
                                    <td class="px-5 py-3 font-semibold {{ $row->type === 'credit' ? 'text-emerald-700' : 'text-red-700' }}">
                                        {{ $row->type === 'credit' ? '+' : '-' }}₹{{ number_format((float) $row->amount, 2) }}
                                        @if($payout)
                                            @php
                                                $payoutStatusClass = match((string) $payout->status) {
                                                    'success' => 'bg-emerald-50 text-emerald-700',
                                                    'failed' => 'bg-red-50 text-red-600',
                                                    default => 'bg-amber-50 text-amber-700',
                                                };
                                            @endphp
                                            <p class="mt-1 text-xs font-medium text-slate-500">Payout ₹{{ number_format((float) $payout->amount, 2) }} · <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $payoutStatusClass }}">{{ ucfirst((string) $payout->status) }}</span></p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        {{ $row->reference ?? '—' }}
                                        @if($payout)
                                            <p class="text-xs font-semibold text-indigo-700">Payout Provider: {{ $providerName ?: '—' }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">{{ $providerName ?: '' }}</td>
                                    <td class="px-5 py-3">@include('livewire.admin.partials.txn-actions', ['id' => $payout?->id, 'reference' => $payout?->reference, 'type' => $payout ? 'payout' : null])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-5 py-3">{{ $ledger->links() }}</div>
                @else
                    <p class="px-6 py-8 text-sm text-slate-400">No ledger entries.</p>
                @endif
            </div>

            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">Top-up Requests</h3>
                </div>
                @if($topups->isNotEmpty())
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Mode</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($topups as $req)
                                <tr>
                                    <td class="px-5 py-3">{{ $req->reference }}</td>
                                    <td class="px-5 py-3">₹{{ number_format((float) $req->amount, 2) }}</td>
                                    <td class="px-5 py-3">{{ $req->payment_mode }}</td>
                                    <td class="px-5 py-3">
                                        @php
                                            $topupClass = match($req->status) {
                                                'approved' => 'bg-emerald-50 text-emerald-700',
                                                'rejected' => 'bg-red-50 text-red-600',
                                                default => 'bg-amber-50 text-amber-700',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $topupClass }}">{{ ucfirst($req->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-5 py-3">{{ $topups->links() }}</div>
                @else
                    <p class="px-6 py-8 text-sm text-slate-400">No top-up requests.</p>
                @endif
            </div>
        </div>
    @endif

    {{-- TRANSACTIONS --}}
    @if($tab === 'transactions')
        <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Total</p><p class="mt-1 text-xl font-bold">{{ $txnSummary['total'] }}</p></div>
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Success</p><p class="mt-1 text-xl font-bold text-emerald-600">{{ $txnSummary['success'] }}</p></div>
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Failed</p><p class="mt-1 text-xl font-bold text-red-600">{{ $txnSummary['failed'] }}</p></div>
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Volume</p><p class="mt-1 text-xl font-bold">₹{{ number_format($txnSummary['volume'], 2) }}</p></div>
        </div>
        <div class="fi-card overflow-hidden">
            @if($transactions->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Payout Provider</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Beneficiary</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Commission</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($transactions as $txn)
                            <tr>
                                <td class="px-5 py-3">{{ $txn->reference }}</td>
                                <td class="px-5 py-3">{{ $txn->type === 'payout' ? (\App\Support\CommissionProviders::name($txn->payout_provider) ?: '—') : '—' }}</td>
                                <td class="px-5 py-3">{{ $txn->beneficiary_name ?? '—' }}</td>
                                <td class="px-5 py-3">₹{{ number_format((float) $txn->amount, 2) }}</td>
                                <td class="px-5 py-3">{{ $txn->commissionEntry ? '₹'.number_format((float) $txn->commissionEntry->commission_amount, 2) : '—' }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $txnStatusClass = match((string) $txn->status) {
                                            'success' => 'bg-emerald-50 text-emerald-700',
                                            'failed' => 'bg-red-50 text-red-600',
                                            default => 'bg-amber-50 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $txnStatusClass }}">{{ ucfirst($txn->status) }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $txn->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-3">@include('livewire.admin.partials.txn-actions', ['id' => $txn->id, 'reference' => $txn->reference, 'type' => $txn->type])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-3">{{ $transactions->links() }}</div>
            @else
                <p class="px-6 py-8 text-sm text-slate-400">No transactions.</p>
            @endif
        </div>
    @endif

    {{-- SETTLEMENTS --}}
    @if($tab === 'settlements')
        <div class="fi-card overflow-hidden">
            @if($settlements->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Fee</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Net</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($settlements as $row)
                            <tr>
                                <td class="px-5 py-3">{{ $row->reference }}</td>
                                <td class="px-5 py-3">₹{{ number_format((float) $row->amount, 2) }}</td>
                                <td class="px-5 py-3">₹{{ number_format((float) $row->fee, 2) }}</td>
                                <td class="px-5 py-3">₹{{ number_format((float) $row->net_amount, 2) }}</td>
                                <td class="px-5 py-3">{{ ucfirst($row->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-3">{{ $settlements->links() }}</div>
            @else
                <p class="px-6 py-8 text-sm text-slate-400">No settlements.</p>
            @endif
        </div>
    @endif

    {{-- BENEFICIARIES --}}
    @if($tab === 'beneficiaries')
        <div class="fi-card overflow-hidden">
            @if($beneficiaries->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Beneficiary name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Account number</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">IFSC</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Bank</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Mobile</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Payout provider</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Last transaction</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($beneficiaries as $b)
                            @php
                                $statusClass = match($b['status']) {
                                    'success' => 'bg-emerald-50 text-emerald-700',
                                    'failed' => 'bg-red-50 text-red-600',
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-3 font-semibold text-slate-900">{{ $b['name'] }}</td>
                                <td class="px-5 py-3 font-mono text-xs">{{ $b['account'] }}</td>
                                <td class="px-5 py-3">{{ $b['ifsc'] }}</td>
                                <td class="px-5 py-3">{{ $b['bank'] }}</td>
                                <td class="px-5 py-3 font-mono text-xs">{{ $b['mobile'] }}</td>
                                <td class="px-5 py-3">{{ $b['provider'] }}</td>
                                <td class="px-5 py-3">{{ $b['last_at'] }}</td>
                                <td class="px-5 py-3">
                                    @if($b['status'])
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $statusClass }}">{{ ucfirst($b['status']) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-3">{{ $beneficiaries->links() }}</div>
            @else
                <p class="px-6 py-8 text-sm text-slate-400">No beneficiaries from this vendor's payout transactions.</p>
            @endif
        </div>
    @endif

    {{-- DEVELOPER --}}
    @if($tab === 'developer')
        <div class="space-y-5">
            <div class="fi-card p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-slate-900">API Access</h3>
                    <button type="button" wire:click="toggleApiAccess" class="fi-btn {{ $vendor->api_enabled ? 'fi-btn-danger' : 'fi-btn-success' }} fi-btn-sm">
                        {{ $vendor->api_enabled ? 'Disable API' : 'Enable API' }}
                    </button>
                </div>
                @if($vendor->apiCredential)
                    @php $c = $vendor->apiCredential; @endphp
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-slate-500">API Key</span><span class="font-mono text-slate-800">{{ $c->api_key }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-500">Webhook</span><span class="text-slate-800">{{ $c->webhook_url ?: '—' }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-500">IP whitelist</span>
                            <span class="text-right text-slate-800">
                                @php $ips = $c->ip_whitelist ?? []; @endphp
                                {{ $ips === [] ? 'Not set (API blocked)' : implode(', ', $ips) }}
                            </span>
                        </div>
                        <div class="flex justify-between gap-4"><span class="text-slate-500">Admin API flag</span><span class="font-semibold {{ $vendor->api_enabled ? 'text-emerald-700' : 'text-red-600' }}">{{ $vendor->api_enabled ? 'Enabled' : 'Disabled' }}</span></div>
                    </div>
                @else
                    <p class="text-sm text-slate-500">No API credentials generated.</p>
                @endif
            </div>
            <div class="fi-card p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Payout API</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Payout Provider: {{ $selectedPayoutProvider['name'] ?? '—' }}</p>
                    </div>
                    @if($payoutAccess?->is_enabled)
                        <button type="button" wire:click="disablePayoutApi" class="fi-btn fi-btn-danger fi-btn-sm">Disable Payout API</button>
                    @else
                        <button type="button" wire:click="enablePayoutApi" class="fi-btn fi-btn-success fi-btn-sm">Assign and enable Payout API</button>
                    @endif
                </div>
                <label class="block max-w-sm text-sm font-semibold text-slate-700">
                    Payout Provider
                    <select wire:model.live="payoutProviderCode" class="fi-input mt-1 text-sm font-normal" aria-label="Payout Provider">
                        @foreach($payoutProviders as $provider)
                            <option value="{{ $provider['code'] }}">{{ $provider['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Provider</span>
                        <span class="font-semibold text-slate-900">{{ $selectedPayoutProvider['name'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">API Access</span>
                        <span class="font-semibold {{ $payoutAccess?->is_enabled ? 'text-emerald-700' : 'text-red-600' }}">{{ $payoutAccess?->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Credential</span>
                        <span class="font-semibold {{ $payoutCredential ? 'text-emerald-700' : 'text-slate-500' }}">{{ $payoutCredential ? 'Generated' : 'Not Generated' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Environment</span>
                        <span class="font-semibold text-slate-900">{{ $selectedPayoutProvider['environment'] ?? '—' }}</span>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Available master APIs</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-700">
                        <li>Banks — GET /api/v1/payout/banks</li>
                        <li>Purposes — GET /api/v1/payout/purposes</li>
                        <li>States — GET /api/v1/payout/states</li>
                    </ul>
                </div>
                @if($payoutAccess?->is_enabled)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        @if($payoutCredential)
                            <div class="flex justify-between gap-4 text-sm">
                                <span class="text-slate-500">API Key</span>
                                <span class="font-mono text-slate-800">{{ $payoutCredential->api_key }}</span>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="button" wire:click="rotatePayoutCredentials" class="fi-btn fi-btn-primary fi-btn-sm">Rotate API secret</button>
                            </div>
                        @else
                            <button type="button" wire:click="generatePayoutCredentials" class="fi-btn fi-btn-primary fi-btn-sm">Generate API Credentials</button>
                        @endif
                        @if($revealedApiSecret)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm">
                                <p class="font-semibold text-amber-900">API Secret — copy now</p>
                                <p class="mt-1 font-mono text-xs text-amber-950">{{ $revealedApiSecret }}</p>
                                <p class="mt-2 text-xs text-amber-800">This secret is shown only once and is not stored in plaintext.</p>
                                <button type="button" wire:click="dismissRevealedApiSecret" class="mt-3 text-xs font-semibold text-amber-900">I have copied the secret</button>
                            </div>
                        @endif
                    </div>
                @endif
                <p class="mt-3 text-xs text-slate-500">Calls still use this vendor's HMAC key. The secret is not shown after you leave this notice.</p>
            </div>
            <div class="fi-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">Assign bank APIs</h3>
                <p class="mb-4 text-xs text-slate-500">Vendor will only see FinPe endpoints for the banks you assign here.</p>
                <div class="space-y-2">
                    @forelse($allBanks as $bank)
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm hover:bg-slate-50">
                            <input type="checkbox" wire:model="assignedBankIds" value="{{ $bank->id }}" class="rounded border-slate-300 text-blue-700">
                            <span>
                                <span class="font-semibold text-slate-800">{{ $bank->name }}</span>
                                <span class="font-mono text-xs text-slate-500"> {{ $bank->code }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">No active banks. Add a bank from the Banks menu first.</p>
                    @endforelse
                </div>
                @if($allBanks->isNotEmpty())
                    <div class="mt-4 flex justify-end">
                        <button type="button" wire:click="saveAssignedBanks" class="fi-btn fi-btn-primary">Save bank assignment</button>
                    </div>
                @endif
            </div>
            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Recent Webhook Logs</h3>
                @forelse($webhookLogs as $log)
                    <div class="mb-3 rounded-lg border border-slate-100 bg-slate-50 p-3 text-xs">
                        <p class="font-semibold text-slate-700">{{ $log->event ?? $log->url ?? 'Webhook' }} · {{ $log->status ?? '' }} · {{ $log->created_at->format('d M Y H:i') }}</p>
                        <p class="mt-1 text-slate-500">{{ \Illuminate\Support\Str::limit((string) ($log->response_body ?? ''), 180) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No webhook logs.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
