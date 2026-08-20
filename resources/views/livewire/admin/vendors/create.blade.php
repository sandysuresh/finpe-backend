<div class="max-w-7xl mx-auto">

    {{-- ============================================================= --}}
    {{-- HEADER --}}
    {{-- ============================================================= --}}

    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Add Vendor
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Complete vendor registration in multiple steps.
                </p>
            </div>

            <a
                href="{{ route('admin.vendors') }}"
                class="fi-btn fi-btn-secondary"
            >
                ← Back to Vendors
            </a>
        </div>
    </div>


    {{-- ============================================================= --}}
    {{-- STEP PROGRESS --}}
    {{-- ============================================================= --}}

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

                    <div class="flex items-center min-w-max">

                        <button
                            type="button"
                            @if($number <= $step && $vendorId)
                                wire:click="goToStep({{ $number }})"
                            @endif
                            class="flex items-center gap-2"
                        >

                            <span
                                class="
                                    w-9 h-9 rounded-full flex items-center justify-center
                                    text-sm font-semibold
                                    border
                                    transition
                                    {{ $step === $number
                                        ? 'bg-blue-700 text-white border-blue-700'
                                        : ($number < $step
                                            ? 'bg-green-100 text-green-700 border-green-300'
                                            : 'bg-white text-slate-400 border-slate-300') }}
                                "
                            >
                                @if($number < $step)
                                    ✓
                                @else
                                    {{ $number }}
                                @endif
                            </span>

                            <span
                                class="
                                    hidden lg:block text-sm font-medium
                                    {{ $step === $number
                                        ? 'text-blue-700'
                                        : ($number < $step
                                            ? 'text-green-700'
                                            : 'text-slate-400') }}
                                "
                            >
                                {{ $label }}
                            </span>

                        </button>

                        @if($number < 7)
                            <div class="w-8 lg:w-12 h-px bg-slate-200 mx-2"></div>
                        @endif

                    </div>

                @endforeach

            </div>
        </div>
    </div>


    {{-- ============================================================= --}}
    {{-- FLASH MESSAGE --}}
    {{-- ============================================================= --}}

    @if(session()->has('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-5">
            <h3 class="font-semibold text-red-800">Please correct the following errors:</h3>
            <ul class="mt-2 list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('livewire.shared.vendor-registration-steps')

</div>