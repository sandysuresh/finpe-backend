<div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Partners
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Manage registered partners and their API access.
            </p>
        </div>

        <a
            href="{{ route('admin.vendors.create') }}"
            class="fi-btn fi-btn-primary"
        >
            <span class="text-lg leading-none">+</span>
            Add Partners
        </a>

    </div>


    {{-- Statistics --}}
    <div class="mb-6 grid grid-cols-4 gap-4">

        {{-- Total --}}
        <div class="fi-card p-5">

            <p class="text-sm font-medium text-slate-500">
                Total Partners
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-900">
                {{ \App\Models\Vendor::count() }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Registered Partners
            </p>

        </div>


        {{-- Active --}}
        <div class="fi-card p-5">

            <p class="text-sm font-medium text-slate-500">
                Active Partners
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ \App\Models\Vendor::where('status', 'active')->count() }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Currently active
            </p>

        </div>


        {{-- KYC --}}
        <div class="fi-card p-5">

            <p class="text-sm font-medium text-slate-500">
                KYC Review
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-500">
                {{ \App\Models\Vendor::whereIn('kyc_status', ['pending', 'submitted'])->count() }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Awaiting verification
            </p>

        </div>


        {{-- API --}}
        <div class="fi-card p-5">

            <p class="text-sm font-medium text-slate-500">
                API Enabled
            </p>

            <p class="mt-2 text-2xl font-bold text-violet-600">
                {{ \App\Models\Vendor::where('api_enabled', true)->count() }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Vendors with API access
            </p>

        </div>

    </div>


    {{-- Main Table Card --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        {{-- Filters --}}
        <div class="border-b border-slate-100 p-4">

            <div class="grid grid-cols-12 gap-3">

                {{-- Search --}}
                <div class="relative col-span-6">

                    <svg
                        class="absolute left-3 top-3 h-4 w-4 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"
                        />
                    </svg>

                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by vendor name, code, email or phone..."
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                    >

                </div>


                {{-- Status --}}
                <div class="col-span-3">

                    <select
                        wire:model.live="status"
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>

                </div>


                {{-- KYC --}}
                <div class="col-span-3">

                    <select
                        wire:model.live="kycStatus"
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                    >
                        <option value="">All KYC Status</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>

                </div>

            </div>

        </div>


        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Vendor
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Contact
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            KYC
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            API
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse($vendors as $vendor)

                        <tr class="hover:bg-slate-50">

                            {{-- Vendor --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-700 font-bold text-white">
                                        {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                                    </div>

                                    <div>

                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $vendor->business_name }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ $vendor->vendor_code }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- Contact --}}
                            <td class="px-6 py-4">

                                <p class="text-sm font-medium text-slate-700">
                                    {{ $vendor->contact_name }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $vendor->email }}
                                </p>

                            </td>


                            {{-- KYC --}}
                            <td class="px-6 py-4">

                                @if($vendor->kyc_status === 'verified')

                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        KYC Approved
                                    </span>

                                @elseif($vendor->kyc_status === 'submitted')

                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        Submitted
                                    </span>

                                @elseif($vendor->kyc_status === 'rejected')

                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        Rejected
                                    </span>

                                @else

                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        Pending
                                    </span>

                                @endif

                            </td>


                            {{-- API --}}
                            <td class="px-6 py-4">

                                @if($vendor->api_enabled)

                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        Enabled
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400">
                                        <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                        Disabled
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @if($vendor->status === 'active')

                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Active
                                    </span>

                                @elseif($vendor->status === 'suspended')

                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        Suspended
                                    </span>

                                @else

                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                        Inactive
                                    </span>

                                @endif

                            </td>


                            {{-- Action --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="fi-btn fi-btn-primary fi-btn-sm">
                                        View
                                    </a>
                                    @if($vendor->registration_step < 7)
                                        <a href="{{ route('admin.vendors.create', $vendor) }}" class="fi-btn fi-btn-secondary fi-btn-sm">
                                            Continue
                                        </a>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="px-6 py-16 text-center">

                                <div class="flex flex-col items-center">

                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8zm8 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                                            />
                                        </svg>
                                    </div>

                                    <p class="mt-4 text-sm font-semibold text-slate-900">
                                        No partners found
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Add a vendor to start managing your API partners.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if($vendors->hasPages())

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $vendors->links() }}
            </div>

        @endif

    </div>

</div>