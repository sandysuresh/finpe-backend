<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Commission Rules</h1>
            <p class="mt-1 text-sm text-slate-500">A rule is chosen by vendor, provider, and type. An AePS merchant rule outranks the vendor rule.</p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canChange): ?>
            <button type="button" wire:click="openCreate" class="fi-btn fi-btn-primary">Add rule</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-6">
        <input wire:model.live.debounce.300ms="search" type="text" class="fi-input text-sm" placeholder="Search name">
        <select wire:model.live="filterVendor" class="fi-input text-sm"><option value="">Vendor</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->business_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <select wire:model.live="filterMerchant" class="fi-input text-sm"><option value="">Merchant</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filterMerchants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $merchant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($merchant->id); ?>"><?php echo e($merchant->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <select wire:model.live="filterProvider" class="fi-input text-sm"><option value="">Provider</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($code); ?>"><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <select wire:model.live="filterType" class="fi-input text-sm"><option value="">Type</option><option value="payout">Payout</option><option value="aeps">AEPS</option></select>
        <select wire:model.live="filterStatus" class="fi-input text-sm"><option value="">Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
    </div>

    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead><tr><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Name','Vendor','Provider','Type','Merchant','Commission type','Value','Priority','Status','']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold"><?php echo e($rule->name); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->vendor->business_name ?? 'Any vendor'); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($providers[$rule->provider] ?? ($rule->provider ?: '—')); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->type === 'aeps' ? 'AEPS' : 'Payout'); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->type === 'aeps' ? ($rule->merchant->code ?? '—') : '—'); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->calc_type); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->value); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->priority ?? '—'); ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo e($rule->status); ?></td>
                        <td class="px-4 py-3 text-right text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canChange): ?>
                                <button type="button" wire:click="openEdit(<?php echo e($rule->id); ?>)" class="text-blue-700">Edit</button>
                                <button type="button" wire:click="toggle(<?php echo e($rule->id); ?>)" class="ml-3 text-slate-600"><?php echo e($rule->status === 'active' ? 'Deactivate' : 'Activate'); ?></button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">No commission rules.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($rules->links()); ?></div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4">
            <form wire:submit="save" class="fi-card w-full max-w-2xl p-6">
                <h2 class="text-lg font-bold"><?php echo e($editingId ? 'Edit rule' : 'New rule'); ?></h2>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label class="text-sm">Name<input wire:model="name" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Vendor<select wire:model.live="vendorId" class="fi-input mt-1 w-full"><option value="">Any vendor</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->business_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label>
                    <label class="text-sm">Provider<select wire:model="provider" class="fi-input mt-1 w-full"><option value="">Select</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($code); ?>"><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label>
                    <label class="text-sm">Type<select wire:model.live="type" class="fi-input mt-1 w-full"><option value="">Select</option><option value="payout">Payout</option><option value="aeps">AEPS</option></select></label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'aeps'): ?>
                        <label class="text-sm">Merchant<select wire:model="merchantId" class="fi-input mt-1 w-full"><option value="">Any merchant</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $merchants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $merchant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($merchant->id); ?>"><?php echo e($merchant->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <label class="text-sm">Commission type<select wire:model="calcType" class="fi-input mt-1 w-full"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></label>
                    <label class="text-sm">Value<input wire:model="value" type="number" step="0.01" min="0" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Priority<input wire:model="priority" type="number" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Effective from<input wire:model="effectiveFrom" type="datetime-local" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Effective to<input wire:model="effectiveTo" type="datetime-local" class="fi-input mt-1 w-full"></label>
                    <label class="text-sm">Status<select wire:model="status" class="fi-input mt-1 w-full"><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><p class="mt-3 text-sm text-red-600"><?php echo e($errors->first()); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)" class="fi-btn">Cancel</button>
                    <button type="submit" class="fi-btn fi-btn-primary">Save</button>
                </div>
            </form>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/commission-rules.blade.php ENDPATH**/ ?>