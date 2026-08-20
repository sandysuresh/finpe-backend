<div class="min-h-full bg-slate-50 p-6">

    
    <div class="mb-6 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Vendors
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Manage registered vendors and their API access.
            </p>
        </div>

        <a
            href="<?php echo e(route('admin.vendors.create')); ?>"
            class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700"
        >
            <span class="text-lg leading-none">+</span>
            Add Vendor
        </a>

    </div>


    
    <div class="mb-6 grid grid-cols-4 gap-4">

        
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Total Vendors
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-900">
                <?php echo e(\App\Models\Vendor::count()); ?>

            </p>

            <p class="mt-1 text-xs text-slate-400">
                Registered vendors
            </p>

        </div>


        
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                Active Vendors
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                <?php echo e(\App\Models\Vendor::where('status', 'active')->count()); ?>

            </p>

            <p class="mt-1 text-xs text-slate-400">
                Currently active
            </p>

        </div>


        
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                KYC Pending
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-500">
                <?php echo e(\App\Models\Vendor::where('kyc_status', 'pending')->count()); ?>

            </p>

            <p class="mt-1 text-xs text-slate-400">
                Awaiting verification
            </p>

        </div>


        
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm font-medium text-slate-500">
                API Enabled
            </p>

            <p class="mt-2 text-2xl font-bold text-violet-600">
                <?php echo e(\App\Models\Vendor::where('api_enabled', true)->count()); ?>

            </p>

            <p class="mt-1 text-xs text-slate-400">
                Vendors with API access
            </p>

        </div>

    </div>


    
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        
        <div class="border-b border-slate-100 p-4">

            <div class="grid grid-cols-12 gap-3">

                
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

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr class="hover:bg-slate-50">

                            
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 font-bold text-violet-700">
                                        <?php echo e(strtoupper(substr($vendor->business_name, 0, 1))); ?>

                                    </div>

                                    <div>

                                        <p class="text-sm font-semibold text-slate-900">
                                            <?php echo e($vendor->business_name); ?>

                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            <?php echo e($vendor->vendor_code); ?>

                                        </p>

                                    </div>

                                </div>

                            </td>


                            
                            <td class="px-6 py-4">

                                <p class="text-sm font-medium text-slate-700">
                                    <?php echo e($vendor->contact_name); ?>

                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    <?php echo e($vendor->email); ?>

                                </p>

                            </td>


                            
                            <td class="px-6 py-4">

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kyc_status === 'verified'): ?>

                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Verified
                                    </span>

                                <?php elseif($vendor->kyc_status === 'submitted'): ?>

                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        Submitted
                                    </span>

                                <?php elseif($vendor->kyc_status === 'rejected'): ?>

                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        Rejected
                                    </span>

                                <?php else: ?>

                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        Pending
                                    </span>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </td>


                            
                            <td class="px-6 py-4">

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->api_enabled): ?>

                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        Enabled
                                    </span>

                                <?php else: ?>

                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400">
                                        <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                        Disabled
                                    </span>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </td>


                            
                            <td class="px-6 py-4">

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->status === 'active'): ?>

                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Active
                                    </span>

                                <?php elseif($vendor->status === 'suspended'): ?>

                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        Suspended
                                    </span>

                                <?php else: ?>

                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                        Inactive
                                    </span>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </td>


                            
                            <td class="px-6 py-4 text-right">

                               
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->registration_step < 7): ?>
                                    <a href="<?php echo e(route('admin.vendors.create', ['vendor' => $vendor->id])); ?>">
                                        Continue Registration
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('admin.vendors.show', $vendor)); ?>">
                                        View
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

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
                                        No vendors found
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Add a vendor to start managing your API partners.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </tbody>

            </table>

        </div>


        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendors->hasPages()): ?>

            <div class="border-t border-slate-100 px-6 py-4">
                <?php echo e($vendors->links()); ?>

            </div>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>

</div><?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/vendors/index.blade.php ENDPATH**/ ?>