<div>
    {{-- ── PAGE HEADER ── --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Profile & KYC</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your account, security, and KYC registration.</p>
    </div>

    {{-- ── TABS ── --}}
    <div class="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
        @foreach(['profile' => 'Profile', 'password' => 'Password', 'kyc' => 'KYC & Registration'] as $t => $label)
            <button wire:click="$set('tab','{{ $t }}')"
                    class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition
                           {{ $tab === $t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ── ALERTS ── --}}
    @if($successMsg)
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            ✓ {{ $successMsg }}
        </div>
    @endif
    @if($errorMsg)
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
            {{ $errorMsg }}
        </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PROFILE --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'profile')
    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-base font-semibold text-slate-900">Business Information</h2>
            <p class="mt-1 text-sm text-slate-500">Update your business profile details.</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business Name <span class="text-red-500">*</span></label>
                    <input wire:model="businessName" type="text" class="fi-input" placeholder="Your business name">
                    @error('businessName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Contact Person <span class="text-red-500">*</span></label>
                    <input wire:model="contactName" type="text" class="fi-input">
                    @error('contactName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Phone <span class="text-red-500">*</span></label>
                    <input wire:model="phone" type="text" class="fi-input">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Country <span class="text-red-500">*</span></label>
                    <input wire:model="country" type="text" class="fi-input">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Email (read-only)</label>
                    <input type="text" readonly value="{{ auth('vendor')->user()->email }}" class="fi-input bg-slate-50 text-slate-400">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Address</label>
                    <textarea wire:model="address" rows="3" class="fi-input" placeholder="Registered business address"></textarea>
                </div>
            </div>
        </div>
        <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
            <button wire:click="saveProfile"
                    class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                Save Profile
            </button>
        </div>
    </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PASSWORD --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'password')
    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-base font-semibold text-slate-900">Change Password</h2>
            <p class="mt-1 text-sm text-slate-500">Choose a strong password with at least 8 characters.</p>
        </div>
        <div class="p-6">
            <div class="max-w-md space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Current Password <span class="text-red-500">*</span></label>
                    <input wire:model="currentPassword" type="password" class="fi-input">
                    @error('currentPassword')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">New Password <span class="text-red-500">*</span></label>
                    <input wire:model="newPassword" type="password" class="fi-input">
                    @error('newPassword')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Confirm New Password <span class="text-red-500">*</span></label>
                    <input wire:model="confirmPassword" type="password" class="fi-input">
                </div>
            </div>
        </div>
        <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
            <button wire:click="changePassword"
                    class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                Update Password
            </button>
        </div>
    </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: KYC — 7-step stepper (read-only view, same design as admin) --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'kyc')

        {{-- KYC Status Banner --}}
        @php
            $kyc = $vendor->kyc_status;
            $regStep = (int) $vendor->registration_step;
            $bannerCls = match($kyc) {
                'verified'  => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'rejected'  => 'border-red-200 bg-red-50 text-red-700',
                'submitted' => 'border-blue-200 bg-blue-50 text-blue-700',
                default     => 'border-amber-200 bg-amber-50 text-amber-800',
            };
            $bannerMsg = match($kyc) {
                'verified'  => '✓ Your KYC is fully verified. All modules are active.',
                'rejected'  => '✗ Your KYC was rejected. Please review and resubmit.',
                'submitted' => '⏳ KYC submitted and under admin review.',
                default     => '⚠ KYC pending — complete all 7 registration steps below.',
            };
        @endphp
        <div class="mb-6 rounded-xl border px-5 py-4 text-sm font-medium {{ $bannerCls }}">
            {{ $bannerMsg }}
        </div>

        {{-- ── STEPPER (same design as admin create) ── --}}
        <div class="fi-card mb-6 overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between gap-2 overflow-x-auto">
                    @php
                        $steps = [
                            1 => 'Registration',
                            2 => 'Legal Details',
                            3 => 'Promoters',
                            4 => 'Directors & IT',
                            5 => 'Business Plan',
                            6 => 'Evaluation',
                            7 => 'Review',
                        ];
                    @endphp
                    @foreach($steps as $number => $label)
                        <div class="flex min-w-max items-center">
                            <button type="button"
                                    @if($number <= $regStep)
                                        wire:click="goToKycStep({{ $number }})"
                                    @endif
                                    class="flex items-center gap-2 {{ $number <= $regStep ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full border text-sm font-semibold transition
                                    {{ $kycStep === $number
                                        ? 'border-violet-600 bg-violet-600 text-white'
                                        : ($number < $kycStep
                                            ? 'border-green-300 bg-green-100 text-green-700'
                                            : ($number <= $regStep
                                                ? 'border-violet-300 bg-violet-50 text-violet-600'
                                                : 'border-slate-300 bg-white text-slate-400')) }}">
                                    {{ $number < $kycStep ? '✓' : $number }}
                                </span>
                                <span class="hidden text-sm font-medium lg:block
                                    {{ $kycStep === $number
                                        ? 'text-violet-700'
                                        : ($number < $kycStep ? 'text-green-700' : 'text-slate-400') }}">
                                    {{ $label }}
                                </span>
                            </button>
                            @if($number < 7)
                                <div class="mx-2 h-px w-8 bg-slate-200 lg:w-12"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        {{-- ── STEP 1: Registration ── --}}
        @if($kycStep === 1)
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Registration Details</h2>
                <p class="mt-1 text-sm text-slate-500">Basic vendor account information.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @foreach([
                        ['Business Name',  $vendor->business_name],
                        ['Contact Person', $vendor->contact_name],
                        ['Email',          $vendor->email],
                        ['Phone',          $vendor->phone],
                        ['Country',        $vendor->country],
                        ['Status',         ucfirst($vendor->status)],
                        ['Vendor Code',    $vendor->vendor_code],
                        ['PMT Code',       $vendor->pmt_code ?? '—'],
                    ] as [$lbl, $val])
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400">{{ $lbl }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $val }}</p>
                        </div>
                    @endforeach
                    @if($vendor->address)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                            <p class="text-xs font-semibold text-slate-400">Address</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $vendor->address }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @if($regStep > 1)
            <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="goToKycStep(2)"
                        class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                    Next: Legal Details →
                </button>
            </div>
            @endif
        </div>
        @endif


        {{-- ── STEP 2: Legal Details ── --}}
        @if($kycStep === 2)
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Legal Details</h2>
                <p class="mt-1 text-sm text-slate-500">Company legal and regulatory information.</p>
            </div>
            <div class="p-6">
                @if($vendor->legalDetails)
                    @php $ld = $vendor->legalDetails; @endphp
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        @foreach([
                            ['Entity Type',               $ld->entity_type ?? '—'],
                            ['Registration Body',         $ld->registration_body ?? '—'],
                            ['Registration Number',       $ld->registration_number ?? '—'],
                            ['Tax Identification',        $ld->tax_identification ?? '—'],
                            ['RBI Regulated',             $ld->rbi_regulated ? 'Yes' : 'No'],
                            ['Incorporation Year',        $ld->incorporation_year ?? '—'],
                            ['Merchant Acquiring Years',  $ld->merchant_acquiring_years ?? '—'],
                        ] as [$lbl, $val])
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400">{{ $lbl }}</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">{{ $val }}</p>
                            </div>
                        @endforeach
                        @if($ld->additional_licenses)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-semibold text-slate-400">Additional Licenses</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">{{ $ld->additional_licenses }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                        <p class="text-sm font-medium text-slate-500">Legal details not submitted yet.</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="goToKycStep(1)" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Back</button>
                @if($regStep > 2)<button wire:click="goToKycStep(3)" class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">Next: Promoters →</button>@endif
            </div>
        </div>
        @endif


        {{-- ── STEP 3: Promoters ── --}}
        @if($kycStep === 3)
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Promoters / Shareholders</h2>
                <p class="mt-1 text-sm text-slate-500">All promoters and shareholders of the entity.</p>
            </div>
            <div class="p-6">
                @if($vendor->promoters->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($vendor->promoters as $i => $p)
                            <div class="rounded-xl border border-slate-200 p-5">
                                <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Promoter {{ $i + 1 }}</p>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    @foreach([
                                        ['Name',             $p->name ?? '—'],
                                        ['Share %',          $p->share_percentage ?? '—'],
                                        ['PAN',              $p->pan ?? '—'],
                                        ['Date of Birth',    $p->dob?->format('d M Y') ?? '—'],
                                        ['Address',          $p->address ?? '—'],
                                    ] as [$lbl, $val])
                                        <div>
                                            <p class="text-xs font-semibold text-slate-400">{{ $lbl }}</p>
                                            <p class="mt-0.5 text-sm font-medium text-slate-800">{{ $val }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                        <p class="text-sm font-medium text-slate-500">No promoters/shareholders submitted yet.</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="goToKycStep(2)" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Back</button>
                @if($regStep > 3)<button wire:click="goToKycStep(4)" class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">Next: Directors & IT →</button>@endif
            </div>
        </div>
        @endif


        {{-- ── STEP 4: Directors & IT ── --}}
        @if($kycStep === 4)
        <div class="space-y-5">
            {{-- Directors --}}
            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-900">Directors / KMP</h2>
                </div>
                <div class="p-6">
                    @if($vendor->directors->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($vendor->directors as $i => $d)
                                <div class="rounded-xl border border-slate-200 p-5">
                                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Director {{ $i + 1 }}</p>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        @foreach([
                                            ['Name',        $d->name ?? '—'],
                                            ['Designation', $d->designation ?? '—'],
                                            ['PAN',         $d->pan_card_no ?? '—'],
                                            ['Date of Birth',$d->date_of_birth?->format('d M Y') ?? '—'],
                                            ['Address',     $d->official_address ?? '—'],
                                        ] as [$lbl, $val])
                                            <div>
                                                <p class="text-xs font-semibold text-slate-400">{{ $lbl }}</p>
                                                <p class="mt-0.5 text-sm font-medium text-slate-800">{{ $val }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center">
                            <p class="text-sm text-slate-500">No directors submitted yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Team & IT --}}
            @if($vendor->teamItDetails)
            @php $ti = $vendor->teamItDetails; @endphp
            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-900">Team & IT Infrastructure</h2>
                </div>
                <div class="p-6">
                    <div class="mb-5">
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Team Size</p>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                            @foreach([
                                ['Total',       $ti->total_employees],
                                ['Technology',  $ti->technology_employees],
                                ['Sales',       $ti->sales_employees],
                                ['Support',     $ti->support_employees],
                                ['Admin/HR',    $ti->admin_finance_hr_employees],
                            ] as [$lbl, $val])
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                                    <p class="text-xl font-bold text-slate-900">{{ $val ?? 0 }}</p>
                                    <p class="text-xs text-slate-400">{{ $lbl }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach([
                            ['Processing Systems', $ti->processing_systems],
                            ['Applications',       $ti->applications],
                            ['Database',           $ti->database_system],
                            ['Switch',             $ti->switch_system],
                            ['Terminals',          $ti->terminals],
                            ['Fraud & Risk',       $ti->fraud_risk_management],
                        ] as [$lbl, $val])
                            @if($val)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold text-slate-400">{{ $lbl }}</p>
                                    <p class="mt-1 text-sm font-medium text-slate-800">{{ $val }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between">
                <button wire:click="goToKycStep(3)" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Back</button>
                @if($regStep > 4)<button wire:click="goToKycStep(5)" class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">Next: Business Plan →</button>@endif
            </div>
        </div>
        @endif


        {{-- ── STEP 5: Business Plan ── --}}
        @if($kycStep === 5)
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Business Plan</h2>
                <p class="mt-1 text-sm text-slate-500">Projected monthly targets and volumes.</p>
            </div>
            <div class="p-6">
                @if($vendor->businessPlans->isNotEmpty())
                    <div class="overflow-x-auto fi-scroll">
                        <table class="min-w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    @foreach(['Month','Customer Registrations','Transactions','Total Volume (₹)'] as $col)
                                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($vendor->businessPlans as $plan)
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-800">{{ $plan->month }}</td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-700">{{ number_format($plan->customer_registrations) }}</td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-700">{{ number_format($plan->transactions) }}</td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-900">₹{{ number_format($plan->total_volume) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                        <p class="text-sm text-slate-500">No business plan submitted yet.</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="goToKycStep(4)" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Back</button>
                @if($regStep > 5)<button wire:click="goToKycStep(6)" class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">Next: Evaluation →</button>@endif
            </div>
        </div>
        @endif


        {{-- ── STEP 6: Evaluation ── --}}
        @if($kycStep === 6)
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Evaluation & Compliance</h2>
                <p class="mt-1 text-sm text-slate-500">Financial and compliance assessment details.</p>
            </div>
            <div class="p-6">
                @if($vendor->evaluation)
                    @php $ev = $vendor->evaluation; @endphp
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        @foreach([
                            ['CA Name',              $ev->ca_name ?? '—'],
                            ['CA Constitution',      $ev->ca_constitution ?? '—'],
                            ['Incorporation Date',   $ev->ca_incorporation_date?->format('d M Y') ?? '—'],
                            ['Net Worth',            $ev->networth ? '₹'.number_format($ev->networth) : '—'],
                            ['Credit Rating',        $ev->credit_rating ?? '—'],
                            ['Bank Since',           $ev->dealing_with_bank_since ?? '—'],
                            ['Contract Expiry',      $ev->contract_expiry_date?->format('d M Y') ?? '—'],
                            ['Engagement Scope',     $ev->engagement_scope ?? '—'],
                            ['Documentation Status', $ev->documentation_status ?? '—'],
                            ['Conflict of Interest', $ev->conflict_of_interest ?? '—'],
                            ['RBI Defaulter',        $ev->rbi_defaulter ?? '—'],
                        ] as [$lbl, $val])
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400">{{ $lbl }}</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">{{ $val }}</p>
                            </div>
                        @endforeach
                        @if($ev->recommendations)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-semibold text-slate-400">Recommendations</p>
                                <p class="mt-1 text-sm font-medium text-slate-800">{{ $ev->recommendations }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                        <p class="text-sm text-slate-500">Evaluation not submitted yet.</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="goToKycStep(5)" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Back</button>
                @if($regStep > 6)<button wire:click="goToKycStep(7)" class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">Next: Review →</button>@endif
            </div>
        </div>
        @endif


        {{-- ── STEP 7: Review / Complete ── --}}
        @if($kycStep === 7)
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">Registration Review</h2>
                <p class="mt-1 text-sm text-slate-500">Summary of your complete registration.</p>
            </div>
            <div class="p-6 space-y-4">

                {{-- Completion Grid --}}
                @php
                    $checks = [
                        [1, 'Registration',   true],
                        [2, 'Legal Details',  (bool) $vendor->legalDetails],
                        [3, 'Promoters',      $vendor->promoters->isNotEmpty()],
                        [4, 'Directors & IT', $vendor->directors->isNotEmpty()],
                        [5, 'Business Plan',  $vendor->businessPlans->isNotEmpty()],
                        [6, 'Evaluation',     (bool) $vendor->evaluation],
                        [7, 'Review',         $regStep >= 7],
                    ];
                @endphp

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($checks as [$num, $lbl, $done])
                        <div class="flex items-center gap-3 rounded-xl border p-4 {{ $done ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold
                                {{ $done ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }}">
                                {{ $done ? '✓' : $num }}
                            </span>
                            <div>
                                <p class="text-xs font-semibold {{ $done ? 'text-emerald-700' : 'text-slate-500' }}">Step {{ $num }}</p>
                                <p class="text-sm font-medium {{ $done ? 'text-emerald-900' : 'text-slate-600' }}">{{ $lbl }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Final KYC Status --}}
                <div class="mt-4 rounded-2xl border-2 {{ $kyc === 'verified' ? 'border-emerald-300 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-6 text-center">
                    @if($kyc === 'verified')
                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-emerald-800">KYC Verified!</h3>
                        <p class="mt-1 text-sm text-emerald-700">Your account is fully active. All modules are enabled.</p>
                    @elseif($kyc === 'submitted')
                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-blue-500">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-blue-800">Under Review</h3>
                        <p class="mt-1 text-sm text-blue-700">Your registration is complete. Admin will review shortly.</p>
                    @else
                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-amber-400">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-amber-800">Pending Admin Verification</h3>
                        <p class="mt-1 text-sm text-amber-700">Registration complete. Contact admin if review takes too long.</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button wire:click="goToKycStep(6)" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Back</button>
            </div>
        </div>
        @endif

    @endif {{-- end kyc tab --}}
</div>
