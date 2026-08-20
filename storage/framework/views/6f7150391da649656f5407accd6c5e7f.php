<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Beneficiaries</h1>
            <p class="mt-1 text-sm text-slate-500">Manage saved bank accounts for faster transfers.</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Beneficiary
        </button>
    </div>

    
    <div class="fi-card mb-5 px-4 py-3">
        <input wire:model.live.debounce.300ms="search" type="text" class="fi-input text-sm"
               placeholder="Search by name or account number...">
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($beneficiaries->isEmpty()): ?>
        <div class="fi-card flex flex-col items-center justify-center py-16 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-5a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-sm font-medium text-slate-700">No beneficiaries yet</p>
            <p class="mt-1 text-xs text-slate-400">Add bank accounts for quick transfers.</p>
            <button wire:click="openCreate" class="mt-4 rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-700">Add Beneficiary</button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $beneficiaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="fi-card p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-base font-bold text-violet-700">
                            <?php echo e(strtoupper(substr($b->name,0,1))); ?>

                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="openEdit(<?php echo e($b->id); ?>)"
                                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="delete(<?php echo e($b->id); ?>)"
                                    wire:confirm="Delete this beneficiary?"
                                    class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-sm font-semibold text-slate-900"><?php echo e($b->name); ?></p>
                        <p class="mt-0.5 font-mono text-xs text-slate-500"><?php echo e($b->account_number); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($b->bank_name): ?>
                            <p class="mt-0.5 text-xs text-slate-400"><?php echo e($b->bank_name); ?><?php echo e($b->ifsc_code ? ' · '.$b->ifsc_code : ''); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($b->mobile): ?>
                            <p class="mt-0.5 text-xs text-slate-400"><?php echo e($b->mobile); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <?php $st = $b->status; $bc = $st==='active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'; ?>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($bc); ?>"><?php echo e(ucfirst($st)); ?></span>
                        <a href="<?php echo e(route('vendor.send-money')); ?>"
                           class="text-xs font-semibold text-violet-600 hover:underline">Send Money →</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="mt-4"><?php echo e($beneficiaries->links()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="fi-card w-full max-w-lg p-6">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900"><?php echo e($editMode ? 'Edit' : 'Add'); ?> Beneficiary</h3>
                <button wire:click="$set('showModal',false)" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Full Name *</label>
                    <input wire:model="name" type="text" class="fi-input text-sm" placeholder="Beneficiary full name">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Account Number *</label>
                    <input wire:model="accountNumber" type="text" class="fi-input text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['accountNumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">IFSC Code</label>
                    <input wire:model="ifscCode" type="text" class="fi-input text-sm" placeholder="SBIN0001234">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Bank Name</label>
                    <input wire:model="bankName" type="text" class="fi-input text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Mobile</label>
                    <input wire:model="mobile" type="text" class="fi-input text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Email</label>
                    <input wire:model="email" type="email" class="fi-input text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="mt-5 flex gap-3 border-t border-slate-100 pt-5">
                <button wire:click="$set('showModal',false)"
                        class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button wire:click="save"
                        class="flex-1 rounded-xl bg-violet-600 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                    <?php echo e($editMode ? 'Update' : 'Save'); ?> Beneficiary
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/beneficiaries.blade.php ENDPATH**/ ?>