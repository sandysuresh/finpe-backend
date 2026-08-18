<div class="max-w-7xl mx-auto">

    
    
    

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
                href="<?php echo e(route('admin.vendors')); ?>"
                class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50"
            >
                ← Back to Vendors
            </a>
        </div>
    </div>


    
    
    

    <div class="fi-card mb-6 overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center justify-between gap-2 overflow-x-auto">

                <?php
                    $steps = [
                        1 => 'Registration',
                        2 => 'Legal Details',
                        3 => 'Promoters',
                        4 => 'Directors & IT',
                        5 => 'Business Plan',
                        6 => 'Evaluation',
                        7 => 'Review',
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $number => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <div class="flex items-center min-w-max">

                        <button
                            type="button"
                            <?php if($number <= $step && $vendorId): ?>
                                wire:click="goToStep(<?php echo e($number); ?>)"
                            <?php endif; ?>
                            class="flex items-center gap-2"
                        >

                            <span
                                class="
                                    w-9 h-9 rounded-full flex items-center justify-center
                                    text-sm font-semibold
                                    border
                                    transition
                                    <?php echo e($step === $number
                                        ? 'bg-purple-600 text-white border-purple-600'
                                        : ($number < $step
                                            ? 'bg-green-100 text-green-700 border-green-300'
                                            : 'bg-white text-slate-400 border-slate-300')); ?>

                                "
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($number < $step): ?>
                                    ✓
                                <?php else: ?>
                                    <?php echo e($number); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>

                            <span
                                class="
                                    hidden lg:block text-sm font-medium
                                    <?php echo e($step === $number
                                        ? 'text-purple-700'
                                        : ($number < $step
                                            ? 'text-green-700'
                                            : 'text-slate-400')); ?>

                                "
                            >
                                <?php echo e($label); ?>

                            </span>

                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($number < 7): ?>
                            <div class="w-8 lg:w-12 h-px bg-slate-200 mx-2"></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </div>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 1): ?>

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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['business_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Country <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model="country"
                            class="fi-input"
                        >

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Status
                        </label>

                        <select wire:model="status" class="fi-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                </div>


                
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

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>


                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                        <p class="text-xs text-purple-600 font-medium">
                            PMT Code
                        </p>

                        <p class="mt-1 text-sm font-semibold text-purple-900">
                            Auto Generated
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
                            Auto Generated
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
                    class="px-6 py-2.5 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 disabled:opacity-60"
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 2): ?>

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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['entity_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['registration_body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['registration_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tax_identification'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Regulated by RBI?
                        </label>

                        <select wire:model="rbi_regulated" class="fi-input">
                            <option value="">Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rbi_regulated'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Year of Incorporation / Commencement
                        </label>

                        <input
                            type="number"
                            wire:model="incorporation_year"
                            class="fi-input"
                            placeholder="YYYY"
                        >

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['incorporation_year'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>


                    
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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['merchant_acquiring_years'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                </div>


                
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

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['additional_licenses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="px-6 py-2.5 rounded-lg bg-purple-600 text-white font-semibold disabled:opacity-60"
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 3): ?>

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
                    class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700"
                >
                    + Add Promoter
                </button>

            </div>


            <div class="p-6 space-y-5">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $promoters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $promoter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <div
                        wire:key="promoter-<?php echo e($index); ?>"
                        class="rounded-xl border border-slate-200 p-5"
                    >

                        <div class="flex justify-between mb-5">

                            <h3 class="font-semibold text-slate-900">
                                Promoter <?php echo e($index + 1); ?>

                            </h3>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($promoters) > 1): ?>

                                <button
                                    type="button"
                                    wire:click="removePromoter(<?php echo e($index); ?>)"
                                    class="text-sm text-red-600 hover:text-red-700"
                                >
                                    Remove
                                </button>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    wire:model="promoters.<?php echo e($index); ?>.name"
                                    class="fi-input"
                                >

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["promoters.$index.name"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>


                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Shareholding % <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="20"
                                    max="100"
                                    wire:model="promoters.<?php echo e($index); ?>.share_percentage"
                                    class="fi-input"
                                >

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["promoters.$index.share_percentage"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>


                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    PAN Card No.
                                </label>

                                <input
                                    type="text"
                                    wire:model="promoters.<?php echo e($index); ?>.pan"
                                    class="fi-input"
                                >

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["promoters.$index.pan"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>


                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Date of Birth
                                </label>

                                <input
                                    type="date"
                                    wire:model="promoters.<?php echo e($index); ?>.dob"
                                    class="fi-input"
                                >

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["promoters.$index.dob"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                        </div>


                        
                        <div class="mt-5">

                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Official Address
                            </label>

                            <textarea
                                wire:model="promoters.<?php echo e($index); ?>.address"
                                rows="3"
                                class="fi-input"
                            ></textarea>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["promoters.$index.address"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="px-6 py-2.5 rounded-lg bg-purple-600 text-white font-semibold disabled:opacity-60"
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 4): ?>

        <div class="space-y-6">

            
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
                        class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700"
                    >
                        + Add Director
                    </button>

                </div>


                <div class="p-6 space-y-5">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $directors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $director): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <div
                            wire:key="director-<?php echo e($index); ?>"
                            class="rounded-xl border border-slate-200 p-5"
                        >

                            <div class="flex justify-between mb-5">

                                <h3 class="font-semibold text-slate-900">
                                    Director <?php echo e($index + 1); ?>

                                </h3>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($directors) > 1): ?>

                                    <button
                                        type="button"
                                        wire:click="removeDirector(<?php echo e($index); ?>)"
                                        class="text-sm text-red-600"
                                    >
                                        Remove
                                    </button>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        wire:model="directors.<?php echo e($index); ?>.name"
                                        class="fi-input"
                                    >

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["directors.$index.name"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Designation <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        wire:model="directors.<?php echo e($index); ?>.designation"
                                        class="fi-input"
                                    >

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["directors.$index.designation"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        PAN Card No.
                                    </label>

                                    <input
                                        type="text"
                                        wire:model="directors.<?php echo e($index); ?>.pan"
                                        class="fi-input"
                                    >
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Date of Birth
                                    </label>

                                    <input
                                        type="date"
                                        wire:model="directors.<?php echo e($index); ?>.dob"
                                        class="fi-input"
                                    >
                                </div>

                            </div>


                            <div class="mt-5">

                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Official Address
                                </label>

                                <textarea
                                    wire:model="directors.<?php echo e($index); ?>.address"
                                    rows="3"
                                    class="fi-input"
                                ></textarea>

                            </div>

                        </div>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


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

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['profile_experience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                </div>

            </div>


            
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

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['total_employees'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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


            
            <div class="fi-card px-6 py-4 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="px-6 py-2.5 rounded-lg bg-purple-600 text-white font-semibold disabled:opacity-60"
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 5): ?>

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

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $business_plan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <tr
                                    wire:key="business-plan-<?php echo e($index); ?>"
                                    class="border-b border-slate-100"
                                >

                                    <td class="px-3 py-3">

                                        <div class="font-medium text-slate-800">
                                            <?php echo e($plan['month']); ?>

                                        </div>

                                    </td>


                                    <td class="px-3 py-3">

                                        <input
                                            type="number"
                                            min="0"
                                            wire:model="business_plan.<?php echo e($index); ?>.customers"
                                            class="fi-input"
                                            placeholder="0"
                                        >

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["business_plan.$index.customers"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-xs text-red-600">
                                                <?php echo e($message); ?>

                                            </p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    </td>


                                    <td class="px-3 py-3">

                                        <input
                                            type="number"
                                            min="0"
                                            wire:model="business_plan.<?php echo e($index); ?>.transactions"
                                            class="fi-input"
                                            placeholder="0"
                                        >

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["business_plan.$index.transactions"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-xs text-red-600">
                                                <?php echo e($message); ?>

                                            </p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    </td>


                                    <td class="px-3 py-3">

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            wire:model="business_plan.<?php echo e($index); ?>.volume"
                                            class="fi-input"
                                            placeholder="0.00"
                                        >

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["business_plan.$index.volume"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-xs text-red-600">
                                                <?php echo e($message); ?>

                                            </p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="px-6 py-2.5 rounded-lg bg-purple-600 text-white font-semibold disabled:opacity-60"
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 6): ?>

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
                            wire:model="ca_incorporation_date"
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
                    class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    class="px-6 py-2.5 rounded-lg bg-purple-600 text-white font-semibold disabled:opacity-60"
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 7): ?>

        <div class="space-y-6">

            <div class="fi-card overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Review & Submit
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Please review the registration details before creating the vendor.
                    </p>

                </div>


                <div class="p-6 space-y-6">

                    
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
                                    <?php echo e($business_name ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Contact Person
                                </p>

                                <p class="font-medium text-slate-800">
                                    <?php echo e($contact_name ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Email
                                </p>

                                <p class="font-medium text-slate-800">
                                    <?php echo e($email ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Phone
                                </p>

                                <p class="font-medium text-slate-800">
                                    <?php echo e($phone ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Country
                                </p>

                                <p class="font-medium text-slate-800">
                                    <?php echo e($country ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Status
                                </p>

                                <p class="font-medium text-slate-800">
                                    <?php echo e(ucfirst($status)); ?>

                                </p>
                            </div>

                        </div>

                    </div>


                    
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
                                    <?php echo e($entity_type ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Registration Number
                                </p>

                                <p class="font-medium">
                                    <?php echo e($registration_number ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    PAN / TIN
                                </p>

                                <p class="font-medium">
                                    <?php echo e($tax_identification ?: '—'); ?>

                                </p>
                            </div>

                        </div>

                    </div>


                    
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

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $promoters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $promoter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <div class="rounded-lg bg-slate-50 p-4">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Name
                                            </p>

                                            <p class="font-medium">
                                                <?php echo e($promoter['name'] ?: '—'); ?>

                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Shareholding
                                            </p>

                                            <p class="font-medium">
                                                <?php echo e($promoter['share_percentage'] ?: '—'); ?>%
                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                PAN
                                            </p>

                                            <p class="font-medium">
                                                <?php echo e($promoter['pan'] ?: '—'); ?>

                                            </p>
                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>

                    </div>


                    
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

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $directors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $director): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <div class="rounded-lg bg-slate-50 p-4">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Name
                                            </p>

                                            <p class="font-medium">
                                                <?php echo e($director['name'] ?: '—'); ?>

                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Designation
                                            </p>

                                            <p class="font-medium">
                                                <?php echo e($director['designation'] ?: '—'); ?>

                                            </p>
                                        </div>


                                        <div>
                                            <p class="text-xs text-slate-500">
                                                PAN
                                            </p>

                                            <p class="font-medium">
                                                <?php echo e($director['pan'] ?: '—'); ?>

                                            </p>
                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>

                    </div>


                    
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
                                    <?php echo e(count($business_plan)); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    First Month
                                </p>

                                <p class="font-medium">
                                    <?php echo e($business_plan[0]['month'] ?? '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Last Month
                                </p>

                                <p class="font-medium">
                                    <?php echo e($business_plan[35]['month'] ?? '—'); ?>

                                </p>
                            </div>

                        </div>

                    </div>


                    
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
                                    <?php echo e($ca_name ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Constitution
                                </p>

                                <p class="font-medium">
                                    <?php echo e($ca_constitution ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Networth
                                </p>

                                <p class="font-medium">
                                    <?php echo e($networth ?: '—'); ?>

                                </p>
                            </div>


                            <div>
                                <p class="text-xs text-slate-500">
                                    Credit Rating
                                </p>

                                <p class="font-medium">
                                    <?php echo e($credit_rating ?: '—'); ?>

                                </p>
                            </div>

                        </div>

                    </div>


                    
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">

                        <div class="flex gap-3">

                            <div class="text-amber-600 text-xl">
                                ⚠
                            </div>

                            <div>

                                <h3 class="font-semibold text-amber-900">
                                    Confirm Vendor Registration
                                </h3>

                                <p class="mt-1 text-sm text-amber-700">
                                    Once you create this vendor, the vendor
                                    account and wallet will be created.
                                    The vendor will be able to login using
                                    Email, PMT Code and Password.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">

                    <button
                        type="button"
                        wire:click="previousStep"
                        class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-medium"
                    >
                        ← Back
                    </button>


                    <button
                        type="button"
                        wire:click="submitRegistration"
                        wire:loading.attr="disabled"
                        wire:target="submitRegistration"
                        class="px-7 py-2.5 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 disabled:opacity-60"
                    >

                        <span wire:loading.remove wire:target="submitRegistration">
                            ✓ Create Vendor
                        </span>

                        <span wire:loading wire:target="submitRegistration">
                            Creating Vendor...
                        </span>

                    </button>

                </div>

            </div>

        </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>

        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5">

            <h3 class="font-semibold text-red-800">
                Please correct the following errors:
            </h3>

            <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-1">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </ul>

        </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /home/sandeep/Documents/Sandeep/finpay/resources/views/livewire/admin/vendors/create.blade.php ENDPATH**/ ?>