    {{-- ============================================================= --}}
    {{-- STEP 1 - REGISTRATION --}}
    {{-- ============================================================= --}}

    @php
        $kycIsAdmin = $kycIsAdmin ?? true;
        $kycLocked = $kycLocked ?? false;
        $maxDate = now()->toDateString();
        $maxYear = now()->year;
    @endphp

    @if($step === 1)

        <div class="fi-card overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">
                    Vendor Registration
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Create the basic vendor account.
                </p>
            </div>


            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Business Name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Business Name <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model="business_name"
                            class="fi-input"
                            placeholder="Enter business name"
                        >

                        @error('business_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Contact Name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Contact Person <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model="contact_name"
                            class="fi-input"
                            placeholder="Contact person name"
                        >

                        @error('contact_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="email"
                            wire:model="email"
                            class="fi-input"
                            placeholder="vendor@example.com"
                        >

                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Phone <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model="phone"
                            class="fi-input"
                            placeholder="Phone number"
                        >

                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Password (admin creates account; vendor may leave blank) --}}
                    @if($kycIsAdmin)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="password"
                            wire:model="password"
                            class="fi-input"
                            placeholder="Minimum 8 characters"
                        >

                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif


                    {{-- Country --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Country <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model="country"
                            class="fi-input"
                        >

                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Status
                        </label>

                        @if($kycIsAdmin)
                            <select wire:model="status" class="fi-input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        @else
                            <input type="text" readonly value="{{ ucfirst($status) }}" class="fi-input bg-slate-50 text-slate-500">
                        @endif

                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>


                {{-- Address --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Address
                    </label>

                    <textarea
                        wire:model="address"
                        rows="3"
                        class="fi-input"
                        placeholder="Vendor registered address"
                    ></textarea>

                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Auto Generated Codes --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                        <p class="text-xs text-purple-600 font-medium">
                            PMT Code
                        </p>

                        <p class="mt-1 text-sm font-semibold text-purple-900">
                            {{ $kycIsAdmin ? 'Auto Generated' : ($vendor?->pmt_code ?? '—') }}
                        </p>

                        <p class="mt-1 text-xs text-purple-600">
                            Vendor will use this code during login.
                        </p>
                    </div>


                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs text-slate-500 font-medium">
                            Vendor Code
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $kycIsAdmin ? 'Auto Generated' : ($vendor?->vendor_code ?? '—') }}
                        </p>
                    </div>

                </div>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="fi-btn fi-btn-primary"
                >
                    <span wire:loading.remove wire:target="nextStep">
                        Continue →
                    </span>

                    <span wire:loading wire:target="nextStep">
                        Saving...
                    </span>
                </button>

            </div>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STEP 2 - LEGAL DETAILS --}}
    {{-- ============================================================= --}}

    @if($step === 2)

        <div class="fi-card overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">
                    Company Legal Details
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Corporate Agent detailed information.
                </p>
            </div>


            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Entity --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Type of Entity <span class="text-red-500">*</span>
                        </label>

                        <select wire:model="entity_type" class="fi-input">
                            <option value="">Select entity type</option>
                            <option value="Private Limited">Private Limited</option>
                            <option value="Public Limited">Public Limited</option>
                            <option value="Partnership">Partnership</option>
                            <option value="LLP">LLP</option>
                            <option value="Proprietorship">Proprietorship</option>
                            <option value="Others">Others</option>
                        </select>

                        @error('entity_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Registration Body --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Registered With
                        </label>

                        <input
                            type="text"
                            wire:model="registration_body"
                            class="fi-input"
                            placeholder="Registration body"
                        >

                        @error('registration_body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Registration Number --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Registration Number & Certificate
                        </label>

                        <input
                            type="text"
                            wire:model="registration_number"
                            class="fi-input"
                            placeholder="Registration number"
                        >

                        @error('registration_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- PAN --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            PAN / TIN
                        </label>

                        <input
                            type="text"
                            wire:model="tax_identification"
                            class="fi-input"
                            placeholder="PAN / TIN"
                        >

                        @error('tax_identification')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- RBI --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Regulated by RBI?
                        </label>

                        <select wire:model="rbi_regulated" class="fi-input">
                            <option value="">Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>

                        @error('rbi_regulated')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Incorporation --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Year of Incorporation / Commencement
                        </label>

                        <input
                            type="number"
                            wire:model="incorporation_year"
                            min="1800"
                            max="{{ $maxYear }}"
                            class="fi-input"
                            placeholder="YYYY"
                        >

                        @error('incorporation_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Merchant acquiring --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Years Engaged in Merchant Acquiring
                        </label>

                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            wire:model="merchant_acquiring_years"
                            class="fi-input"
                            placeholder="Years"
                        >

                        @error('merchant_acquiring_years')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>


                {{-- Additional Licenses --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Additional Licenses / Registrations
                    </label>

                    <textarea
                        wire:model="additional_licenses"
                        rows="4"
                        class="fi-input"
                        placeholder="Enter additional licenses or registrations"
                    ></textarea>

                    @error('additional_licenses')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="fi-btn fi-btn-secondary"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="fi-btn fi-btn-primary"
                >
                    <span wire:loading.remove wire:target="nextStep">
                        Continue →
                    </span>

                    <span wire:loading wire:target="nextStep">
                        Saving...
                    </span>
                </button>

            </div>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STEP 3 - PROMOTERS --}}
    {{-- ============================================================= --}}

    @if($step === 3)

        <div class="fi-card overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between gap-4">

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        Promoters / Shareholders
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Add promoter and shareholder information.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="addPromoter"
                    class="fi-btn fi-btn-primary fi-btn-sm"
                >
                    + Add Promoter
                </button>

            </div>


            <div class="p-6 space-y-5">

                @foreach($promoters as $index => $promoter)

                    <div
                        wire:key="promoter-{{ $index }}"
                        class="rounded-xl border border-slate-200 p-5"
                    >

                        <div class="flex justify-between mb-5">

                            <h3 class="font-semibold text-slate-900">
                                Promoter {{ $index + 1 }}
                            </h3>

                            @if(count($promoters) > 1)

                                <button
                                    type="button"
                                    wire:click="removePromoter({{ $index }})"
                                    class="text-sm text-red-600 hover:text-red-700"
                                >
                                    Remove
                                </button>

                            @endif

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    wire:model="promoters.{{ $index }}.name"
                                    class="fi-input"
                                >

                                @error("promoters.$index.name")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            {{-- Share --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Shareholding % <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="20"
                                    max="100"
                                    wire:model.live="promoters.{{ $index }}.share_percentage"
                                    class="fi-input"
                                >

                                @error("promoters.$index.share_percentage")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            {{-- PAN --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    PAN Card No.
                                </label>

                                <input
                                    type="text"
                                    wire:model="promoters.{{ $index }}.pan"
                                    class="fi-input"
                                >

                                @error("promoters.$index.pan")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            {{-- DOB --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Date of Birth <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="promoters.{{ $index }}.dob"
                                    min="1900-01-01"
                                    max="{{ $maxDate }}"
                                    class="fi-input"
                                >

                                @error("promoters.$index.dob")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>


                        {{-- Address --}}
                        <div class="mt-5">

                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Official Address
                            </label>

                            <textarea
                                wire:model="promoters.{{ $index }}.address"
                                rows="3"
                                class="fi-input"
                            ></textarea>

                            @error("promoters.$index.address")
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                        </div>

                    </div>

                @endforeach

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="fi-btn fi-btn-secondary"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="fi-btn fi-btn-primary"
                >
                    <span wire:loading.remove wire:target="nextStep">
                        Continue →
                    </span>

                    <span wire:loading wire:target="nextStep">
                        Saving...
                    </span>
                </button>

            </div>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STEP 4 - DIRECTORS + TEAM + IT --}}
    {{-- ============================================================= --}}

    @if($step === 4)

        <div class="space-y-6">

            {{-- Directors --}}
            <div class="fi-card overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            Directors / KMP
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Add directors and key management personnel.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="addDirector"
                        class="fi-btn fi-btn-primary fi-btn-sm"
                    >
                        + Add Director
                    </button>

                </div>


                <div class="p-6 space-y-5">

                    @foreach($directors as $index => $director)

                        <div
                            wire:key="director-{{ $index }}"
                            class="rounded-xl border border-slate-200 p-5"
                        >

                            <div class="flex justify-between mb-5">

                                <h3 class="font-semibold text-slate-900">
                                    Director {{ $index + 1 }}
                                </h3>

                                @if(count($directors) > 1)

                                    <button
                                        type="button"
                                        wire:click="removeDirector({{ $index }})"
                                        class="text-sm text-red-600"
                                    >
                                        Remove
                                    </button>

                                @endif

                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        wire:model="directors.{{ $index }}.name"
                                        class="fi-input"
                                    >

                                    @error("directors.$index.name")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Designation <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        wire:model="directors.{{ $index }}.designation"
                                        class="fi-input"
                                    >

                                    @error("directors.$index.designation")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        PAN Card No.
                                    </label>

                                    <input
                                        type="text"
                                        wire:model="directors.{{ $index }}.pan"
                                        class="fi-input"
                                    >
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        wire:model.live="directors.{{ $index }}.dob"
                                        min="1900-01-01"
                                        max="{{ $maxDate }}"
                                        class="fi-input"
                                    >
                                </div>

                            </div>


                            <div class="mt-5">

                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Official Address
                                </label>

                                <textarea
                                    wire:model="directors.{{ $index }}.address"
                                    rows="3"
                                    class="fi-input"
                                ></textarea>

                            </div>

                        </div>

                    @endforeach


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Profile & Past Experience
                        </label>

                        <textarea
                            wire:model="profile_experience"
                            rows="4"
                            class="fi-input"
                            placeholder="Profile and past experience"
                        ></textarea>

                        @error('profile_experience')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>


            {{-- Team --}}
            <div class="fi-card overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Team Structure
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Provide current employee distribution.
                    </p>

                </div>


                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Total Employees
                            </label>

                            <input
                                type="number"
                                min="0"
                                wire:model="total_employees"
                                class="fi-input"
                            >

                            @error('total_employees')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Technology
                            </label>

                            <input
                                type="number"
                                min="0"
                                wire:model="technology_employees"
                                class="fi-input"
                            >
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Sales
                            </label>

                            <input
                                type="number"
                                min="0"
                                wire:model="sales_employees"
                                class="fi-input"
                            >
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Support
                            </label>

                            <input
                                type="number"
                                min="0"
                                wire:model="support_employees"
                                class="fi-input"
                            >
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Admin / Finance / HR
                            </label>

                            <input
                                type="number"
                                min="0"
                                wire:model="admin_finance_hr_employees"
                                class="fi-input"
                            >
                        </div>

                    </div>

                </div>

            </div>


            {{-- IT --}}
            <div class="fi-card overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        IT & Technology Infrastructure
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Provide technology and system details.
                    </p>

                </div>


                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Processing Systems
                        </label>

                        <textarea
                            wire:model="processing_systems"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Applications
                        </label>

                        <textarea
                            wire:model="applications"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Database
                        </label>

                        <textarea
                            wire:model="database"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Switch
                        </label>

                        <textarea
                            wire:model="switch"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Terminals
                        </label>

                        <textarea
                            wire:model="terminals"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Fraud / Risk Systems
                        </label>

                        <textarea
                            wire:model="fraud_risk_systems"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Merchant / Agent Management Systems
                        </label>

                        <textarea
                            wire:model="merchant_agent_management_systems"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Merchant / Agent Portal
                        </label>

                        <textarea
                            wire:model="merchant_agent_portal"
                            rows="3"
                            class="fi-input"
                        ></textarea>
                    </div>

                </div>

            </div>


            {{-- Navigation --}}
            <div class="fi-card px-6 py-4 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="fi-btn fi-btn-secondary"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="fi-btn fi-btn-primary"
                >
                    <span wire:loading.remove wire:target="nextStep">
                        Continue →
                    </span>

                    <span wire:loading wire:target="nextStep">
                        Saving...
                    </span>
                </button>

            </div>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STEP 5 - BUSINESS PLAN --}}
    {{-- ============================================================= --}}

    @if($step === 5)

        <div class="fi-card overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="text-lg font-semibold text-slate-900">
                    3 Years Business Plan
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Enter projected customers, transactions and volume for each month.
                </p>

            </div>


            <div class="p-6">

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr class="border-b border-slate-200 text-left">

                                <th class="px-3 py-3 font-semibold text-slate-700">
                                    Month
                                </th>

                                <th class="px-3 py-3 font-semibold text-slate-700">
                                    Customers
                                </th>

                                <th class="px-3 py-3 font-semibold text-slate-700">
                                    Transactions
                                </th>

                                <th class="px-3 py-3 font-semibold text-slate-700">
                                    Volume
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($business_plan as $index => $plan)

                                <tr
                                    wire:key="business-plan-{{ $index }}"
                                    class="border-b border-slate-100"
                                >

                                    <td class="px-3 py-3">

                                        <div class="font-medium text-slate-800">
                                            {{ $plan['month'] }}
                                        </div>

                                    </td>


                                    <td class="px-3 py-3">

                                        <input
                                            type="number"
                                            min="0"
                                            wire:model="business_plan.{{ $index }}.customers"
                                            class="fi-input"
                                            placeholder="0"
                                        >

                                        @error("business_plan.$index.customers")
                                            <p class="mt-1 text-xs text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </td>


                                    <td class="px-3 py-3">

                                        <input
                                            type="number"
                                            min="0"
                                            wire:model="business_plan.{{ $index }}.transactions"
                                            class="fi-input"
                                            placeholder="0"
                                        >

                                        @error("business_plan.$index.transactions")
                                            <p class="mt-1 text-xs text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </td>


                                    <td class="px-3 py-3">

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            wire:model="business_plan.{{ $index }}.volume"
                                            class="fi-input"
                                            placeholder="0.00"
                                        >

                                        @error("business_plan.$index.volume")
                                            <p class="mt-1 text-xs text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="fi-btn fi-btn-secondary"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="fi-btn fi-btn-primary"
                >
                    <span wire:loading.remove wire:target="nextStep">
                        Continue →
                    </span>

                    <span wire:loading wire:target="nextStep">
                        Saving...
                    </span>
                </button>

            </div>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STEP 6 - EVALUATION --}}
    {{-- ============================================================= --}}

    @if($step === 6)

        <div class="fi-card overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="text-lg font-semibold text-slate-900">
                    Evaluation
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Complete financial, compliance and evaluation information.
                </p>

            </div>


            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Name of CA
                        </label>

                        <input
                            type="text"
                            wire:model="ca_name"
                            class="fi-input"
                            placeholder="Name of CA"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Constitution of CA
                        </label>

                        <input
                            type="text"
                            wire:model="ca_constitution"
                            class="fi-input"
                            placeholder="Constitution of CA"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            CA Incorporation Date
                        </label>

                        <input
                            type="date"
                            wire:model.live="ca_incorporation_date"
                            min="1900-01-01"
                            max="{{ $maxDate }}"
                            class="fi-input"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Networth / Financial Strength
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="networth"
                            class="fi-input"
                            placeholder="0.00"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            External Credit Rating
                        </label>

                        <input
                            type="text"
                            wire:model="credit_rating"
                            class="fi-input"
                            placeholder="Credit rating"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Dealing with Bank Since
                        </label>

                        <input
                            type="text"
                            wire:model="dealing_with_bank_since"
                            class="fi-input"
                            placeholder="Year / Date"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Contract Expiry Date
                        </label>

                        <input
                            type="date"
                            wire:model="contract_expiry_date"
                            class="fi-input"
                        >
                    </div>

                </div>


                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Engagement Scope
                    </label>

                    <textarea
                        wire:model="engagement_scope"
                        rows="4"
                        class="fi-input"
                        placeholder="Detailed description of type and scope of engagement"
                    ></textarea>
                </div>


                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Open Risk Issues
                    </label>

                    <textarea
                        wire:model="open_risk_issues"
                        rows="4"
                        class="fi-input"
                        placeholder="Reputation, compliance, data security etc."
                    ></textarea>
                </div>


                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Documentation Status
                    </label>

                    <textarea
                        wire:model="documentation_status"
                        rows="3"
                        class="fi-input"
                        placeholder="Status of CA documentation"
                    ></textarea>
                </div>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Conflict of Interest
                        </label>

                        <textarea
                            wire:model="conflict_of_interest"
                            rows="4"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Termination / Penalties
                        </label>

                        <textarea
                            wire:model="terminated_or_penalties"
                            rows="4"
                            class="fi-input"
                        ></textarea>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            RBI Defaulter Status
                        </label>

                        <textarea
                            wire:model="rbi_defaulter"
                            rows="4"
                            class="fi-input"
                        ></textarea>
                    </div>

                </div>


                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Recommendations
                    </label>

                    <textarea
                        wire:model="recommendations"
                        rows="5"
                        class="fi-input"
                        placeholder="Recommendations"
                    ></textarea>
                </div>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="fi-btn fi-btn-secondary"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="fi-btn fi-btn-primary"
                >
                    <span wire:loading.remove wire:target="nextStep">
                        Review →
                    </span>

                    <span wire:loading wire:target="nextStep">
                        Saving...
                    </span>
                </button>

            </div>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STEP 7 - REVIEW --}}
    {{-- ============================================================= --}}

    @if($step === 7)

        <div class="space-y-6">

            <div class="fi-card overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Review & Submit
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $kycIsAdmin
                            ? 'Please review the registration details before creating the vendor.'
                            : 'Please review your KYC details. Submit enable tabhi hoga jab steps 1–6 complete honge.' }}
                    </p>

                </div>


                <div class="p-6 space-y-6">

                    {{-- Registration --}}
                    <div class="rounded-xl border border-slate-200 p-5">

                        <div class="flex items-center justify-between mb-4">

                            <h3 class="font-semibold text-slate-900">
                                1. Vendor Registration
                            </h3>

                            <button
                                type="button"
                                wire:click="goToStep(1)"
                                class="text-sm text-purple-600 font-medium"
                            >
                                Edit
                            </button>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                            <div>
                                <p class="text-xs text-slate-500">
                                    Business Name
                                </p>

                                <p class="font-medium text-slate-800">
                                    {{ $business_name ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Contact Person
                                </p>

                                <p class="font-medium text-slate-800">
                                    {{ $contact_name ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Email
                                </p>

                                <p class="font-medium text-slate-800">
                                    {{ $email ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Phone
                                </p>

                                <p class="font-medium text-slate-800">
                                    {{ $phone ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Country
                                </p>

                                <p class="font-medium text-slate-800">
                                    {{ $country ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Status
                                </p>

                                <p class="font-medium text-slate-800">
                                    {{ ucfirst($status) }}
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Legal --}}
                    <div class="rounded-xl border border-slate-200 p-5">

                        <div class="flex items-center justify-between mb-4">

                            <h3 class="font-semibold text-slate-900">
                                2. Legal Details
                            </h3>

                            <button
                                type="button"
                                wire:click="goToStep(2)"
                                class="text-sm text-purple-600 font-medium"
                            >
                                Edit
                            </button>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                            <div>
                                <p class="text-xs text-slate-500">
                                    Entity Type
                                </p>

                                <p class="font-medium">
                                    {{ $entity_type ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Registration Number
                                </p>

                                <p class="font-medium">
                                    {{ $registration_number ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    PAN / TIN
                                </p>

                                <p class="font-medium">
                                    {{ $tax_identification ?: '—' }}
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Promoters --}}
                    <div class="rounded-xl border border-slate-200 p-5">

                        <div class="flex items-center justify-between mb-4">

                            <h3 class="font-semibold text-slate-900">
                                3. Promoters / Shareholders
                            </h3>

                            <button
                                type="button"
                                wire:click="goToStep(3)"
                                class="text-sm text-purple-600 font-medium"
                            >
                                Edit
                            </button>

                        </div>


                        <div class="space-y-3">

                            @foreach($promoters as $index => $promoter)

                                <div class="rounded-lg bg-slate-50 p-4">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Name
                                            </p>

                                            <p class="font-medium">
                                                {{ $promoter['name'] ?: '—' }}
                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Shareholding
                                            </p>

                                            <p class="font-medium">
                                                {{ $promoter['share_percentage'] ?: '—' }}%
                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                PAN
                                            </p>

                                            <p class="font-medium">
                                                {{ $promoter['pan'] ?: '—' }}
                                            </p>
                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>


                    {{-- Directors --}}
                    <div class="rounded-xl border border-slate-200 p-5">

                        <div class="flex items-center justify-between mb-4">

                            <h3 class="font-semibold text-slate-900">
                                4. Directors / KMP
                            </h3>

                            <button
                                type="button"
                                wire:click="goToStep(4)"
                                class="text-sm text-purple-600 font-medium"
                            >
                                Edit
                            </button>

                        </div>


                        <div class="space-y-3">

                            @foreach($directors as $index => $director)

                                <div class="rounded-lg bg-slate-50 p-4">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Name
                                            </p>

                                            <p class="font-medium">
                                                {{ $director['name'] ?: '—' }}
                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Designation
                                            </p>

                                            <p class="font-medium">
                                                {{ $director['designation'] ?: '—' }}
                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                PAN
                                            </p>

                                            <p class="font-medium">
                                                {{ $director['pan'] ?: '—' }}
                                            </p>
                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>


                    {{-- Business Plan --}}
                    <div class="rounded-xl border border-slate-200 p-5">

                        <div class="flex items-center justify-between mb-4">

                            <h3 class="font-semibold text-slate-900">
                                5. Business Plan
                            </h3>

                            <button
                                type="button"
                                wire:click="goToStep(5)"
                                class="text-sm text-purple-600 font-medium"
                            >
                                Edit
                            </button>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                            <div>
                                <p class="text-xs text-slate-500">
                                    Plan Months
                                </p>

                                <p class="font-medium">
                                    {{ count($business_plan) }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    First Month
                                </p>

                                <p class="font-medium">
                                    {{ $business_plan[0]['month'] ?? '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Last Month
                                </p>

                                <p class="font-medium">
                                    {{ $business_plan[35]['month'] ?? '—' }}
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Evaluation --}}
                    <div class="rounded-xl border border-slate-200 p-5">

                        <div class="flex items-center justify-between mb-4">

                            <h3 class="font-semibold text-slate-900">
                                6. Evaluation
                            </h3>

                            <button
                                type="button"
                                wire:click="goToStep(6)"
                                class="text-sm text-purple-600 font-medium"
                            >
                                Edit
                            </button>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

                            <div>
                                <p class="text-xs text-slate-500">
                                    Name of CA
                                </p>

                                <p class="font-medium">
                                    {{ $ca_name ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Constitution
                                </p>

                                <p class="font-medium">
                                    {{ $ca_constitution ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Networth
                                </p>

                                <p class="font-medium">
                                    {{ $networth ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Credit Rating
                                </p>

                                <p class="font-medium">
                                    {{ $credit_rating ?: '—' }}
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Final warning --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">

                        <div class="flex gap-3">

                            <div class="text-amber-600 text-xl">
                                ⚠
                            </div>

                            <div>

                                <h3 class="font-semibold text-amber-900">
                                    {{ $kycIsAdmin ? 'Confirm Vendor Registration' : 'Confirm KYC Submission' }}
                                </h3>

                                <p class="mt-1 text-sm text-amber-700">
                                    @if($kycIsAdmin)
                                        Once you create this vendor, the vendor
                                        account and wallet will be created.
                                        The vendor will be able to login using
                                        Email, PMT Code and Password.
                                    @else
                                        Once submitted, your KYC will be sent
                                        for admin review. You can still update
                                        details until it is verified.
                                    @endif
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                    <button
                        type="button"
                        wire:click="previousStep"
                        class="fi-btn fi-btn-secondary"
                    >
                        ← Back
                    </button>


                    @unless($kycLocked)
                    @php $canSubmitKyc = $kycIsAdmin || $this->kycCanSubmit(); @endphp
                    <button
                        type="button"
                        wire:click="submitRegistration"
                        @disabled(! $canSubmitKyc)
                        wire:loading.attr="disabled"
                        wire:target="submitRegistration"
                        class="fi-btn fi-btn-success"
                        title="{{ $canSubmitKyc ? '' : 'Complete all KYC steps before submitting' }}"
                    >

                        <span wire:loading.remove wire:target="submitRegistration">
                            {{ $kycIsAdmin ? '✓ Create Vendor' : '✓ Submit KYC' }}
                        </span>

                        <span wire:loading wire:target="submitRegistration">
                            {{ $kycIsAdmin ? 'Creating Vendor...' : 'Submitting...' }}
                        </span>

                    </button>
                    @endunless

                </div>

            </div>

        </div>

    @endif
