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
    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-5"
             x-data x-init="$el.scrollIntoView({behavior: 'smooth', block: 'start'})">
            <h3 class="font-semibold text-red-800">Please correct the following errors:</h3>
            <ul class="mt-2 list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                    <input wire:model="business_name" type="text" class="fi-input" placeholder="Your business name">
                    @error('business_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Contact Person <span class="text-red-500">*</span></label>
                    <input wire:model="contact_name" type="text" class="fi-input">
                    @error('contact_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
            <button wire:click="saveProfile" class="fi-btn fi-btn-primary">
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
            <button wire:click="changePassword" class="fi-btn fi-btn-primary">
                Update Password
            </button>
        </div>
    </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: KYC — same 7-step form as admin vendor registration --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'kyc')

        @php
            $kyc = $vendor->kyc_status;
            $bannerCls = match($kyc) {
                'verified'  => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'rejected'  => 'border-red-200 bg-red-50 text-red-700',
                'submitted' => 'border-blue-200 bg-blue-50 text-blue-700',
                default     => 'border-amber-200 bg-amber-50 text-amber-800',
            };
            $bannerMsg = match($kyc) {
                'verified'  => '✓ KYC Approved — your account is fully verified.',
                'rejected'  => '✗ Your KYC was rejected. Please review the comment and resubmit.',
                'submitted' => '⏳ KYC submitted and under admin review. You can view all steps below.',
                default     => 'KYC pending — har step mandatory hai. Aap upar se koi bhi step dekh sakte hain, lekin Submit tabhi hoga jab saara data complete ho.',
            };
        @endphp
        <div class="mb-6 rounded-xl border px-5 py-4 text-sm font-medium {{ $bannerCls }}">
            {{ $bannerMsg }}
            @if($vendor->kycReviews->isNotEmpty())
                <div class="mt-3 space-y-2">
                    @foreach($vendor->kycReviews as $review)
                        @if($review->comment)
                            <p class="text-sm font-normal">
                                {{ $review->created_at->format('d M Y H:i') }}
                                · {{ $review->action === 'rejected' ? 'Rejected' : ($review->action === 'approved' ? 'Approved' : 'Update') }}:
                                {{ $review->comment }}
                            </p>
                        @endif
                    @endforeach
                </div>
            @elseif($vendor->kyc_comment)
                <p class="mt-2 text-sm font-normal">Admin comment: {{ $vendor->kyc_comment }}</p>
            @endif
        </div>

        @php $incompleteSteps = $this->incompleteKycSteps(); @endphp
        @if($kyc === 'pending' || $kyc === 'rejected')
            <div class="mb-6 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                @foreach([1=>'Registration',2=>'Legal',3=>'Promoters',4=>'Directors',5=>'Business Plan',6=>'Evaluation'] as $n => $lbl)
                    @php $done = ! in_array($n, $incompleteSteps, true); @endphp
                    <button type="button" wire:click="goToStep({{ $n }})"
                            class="rounded-xl border px-3 py-2.5 text-left transition {{ $done ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                        <p class="text-[11px] font-semibold {{ $done ? 'text-emerald-700' : 'text-amber-700' }}">Step {{ $n }}</p>
                        <p class="mt-0.5 text-xs font-medium {{ $done ? 'text-emerald-900' : 'text-amber-900' }}">{{ $lbl }} · {{ $done ? 'Done' : 'Required' }}</p>
                    </button>
                @endforeach
            </div>
        @endif

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
                        @php
                            $stepDone = $number === 7
                                ? $incompleteSteps === []
                                : ! in_array($number, $incompleteSteps, true);
                        @endphp
                        <div class="flex min-w-max items-center">
                            <button type="button"
                                    wire:click="goToStep({{ $number }})"
                                    class="flex items-center gap-2">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full border text-sm font-semibold transition
                                    {{ $step === $number
                                        ? 'border-violet-600 bg-violet-600 text-white'
                                        : ($stepDone
                                            ? 'border-green-300 bg-green-100 text-green-700'
                                            : 'border-slate-300 bg-white text-slate-400') }}">
                                    {{ $stepDone && $step !== $number ? '✓' : $number }}
                                </span>
                                <span class="hidden text-sm font-medium lg:block
                                    {{ $step === $number
                                        ? 'text-violet-700'
                                        : ($stepDone ? 'text-green-700' : 'text-slate-400') }}">
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

        @include('livewire.shared.vendor-registration-steps')

    @endif {{-- end kyc tab --}}
</div>
