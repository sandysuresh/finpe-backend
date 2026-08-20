<div>
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Profile & KYC</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your account, security, and KYC registration.</p>
    </div>

    
    <div class="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['profile' => 'Profile', 'password' => 'Password', 'kyc' => 'KYC & Registration']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button wire:click="$set('tab','<?php echo e($t); ?>')"
                    class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition
                           <?php echo e($tab === $t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">
                <?php echo e($label); ?>

            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($successMsg): ?>
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            ✓ <?php echo e($successMsg); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMsg): ?>
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
            <?php echo e($errorMsg); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-5"
             x-data x-init="$el.scrollIntoView({behavior: 'smooth', block: 'start'})">
            <h3 class="font-semibold text-red-800">Please correct the following errors:</h3>
            <ul class="mt-2 list-disc list-inside space-y-1 text-sm text-red-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'profile'): ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['business_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Contact Person <span class="text-red-500">*</span></label>
                    <input wire:model="contact_name" type="text" class="fi-input">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Phone <span class="text-red-500">*</span></label>
                    <input wire:model="phone" type="text" class="fi-input">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Country <span class="text-red-500">*</span></label>
                    <input wire:model="country" type="text" class="fi-input">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Email (read-only)</label>
                    <input type="text" readonly value="<?php echo e(auth('vendor')->user()->email); ?>" class="fi-input bg-slate-50 text-slate-400">
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'password'): ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['currentPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">New Password <span class="text-red-500">*</span></label>
                    <input wire:model="newPassword" type="password" class="fi-input">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'kyc'): ?>

        <?php
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
        ?>
        <div class="mb-6 rounded-xl border px-5 py-4 text-sm font-medium <?php echo e($bannerCls); ?>">
            <?php echo e($bannerMsg); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kycReviews->isNotEmpty()): ?>
                <div class="mt-3 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendor->kycReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->comment): ?>
                            <p class="text-sm font-normal">
                                <?php echo e($review->created_at->format('d M Y H:i')); ?>

                                · <?php echo e($review->action === 'rejected' ? 'Rejected' : ($review->action === 'approved' ? 'Approved' : 'Update')); ?>:
                                <?php echo e($review->comment); ?>

                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif($vendor->kyc_comment): ?>
                <p class="mt-2 text-sm font-normal">Admin comment: <?php echo e($vendor->kyc_comment); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php $incompleteSteps = $this->incompleteKycSteps(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kyc === 'pending' || $kyc === 'rejected'): ?>
            <div class="mb-6 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1=>'Registration',2=>'Legal',3=>'Promoters',4=>'Directors',5=>'Business Plan',6=>'Evaluation']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $done = ! in_array($n, $incompleteSteps, true); ?>
                    <button type="button" wire:click="goToStep(<?php echo e($n); ?>)"
                            class="rounded-xl border px-3 py-2.5 text-left transition <?php echo e($done ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'); ?>">
                        <p class="text-[11px] font-semibold <?php echo e($done ? 'text-emerald-700' : 'text-amber-700'); ?>">Step <?php echo e($n); ?></p>
                        <p class="mt-0.5 text-xs font-medium <?php echo e($done ? 'text-emerald-900' : 'text-amber-900'); ?>"><?php echo e($lbl); ?> · <?php echo e($done ? 'Done' : 'Required'); ?></p>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
                        <?php
                            $stepDone = $number === 7
                                ? $incompleteSteps === []
                                : ! in_array($number, $incompleteSteps, true);
                        ?>
                        <div class="flex min-w-max items-center">
                            <button type="button"
                                    wire:click="goToStep(<?php echo e($number); ?>)"
                                    class="flex items-center gap-2">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full border text-sm font-semibold transition
                                    <?php echo e($step === $number
                                        ? 'border-violet-600 bg-violet-600 text-white'
                                        : ($stepDone
                                            ? 'border-green-300 bg-green-100 text-green-700'
                                            : 'border-slate-300 bg-white text-slate-400')); ?>">
                                    <?php echo e($stepDone && $step !== $number ? '✓' : $number); ?>

                                </span>
                                <span class="hidden text-sm font-medium lg:block
                                    <?php echo e($step === $number
                                        ? 'text-violet-700'
                                        : ($stepDone ? 'text-green-700' : 'text-slate-400')); ?>">
                                    <?php echo e($label); ?>

                                </span>
                            </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($number < 7): ?>
                                <div class="mx-2 h-px w-8 bg-slate-200 lg:w-12"></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <?php echo $__env->make('livewire.shared.vendor-registration-steps', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> 
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/profile.blade.php ENDPATH**/ ?>