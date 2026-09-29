<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Merchants</h1>
        <p class="mt-1 text-sm text-slate-500">AePS merchants registered under a partner.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search merchant or partner...">
    </div>

    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead>
                <tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Merchant','Partner','Phone','Status','Applied entries','Applied total']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $merchants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $merchant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-5 py-3 text-sm">
                            <div class="font-semibold text-slate-900"><?php echo e(trim($merchant->first_name.' '.$merchant->last_name) ?: $merchant->code); ?></div>
                            <div class="text-xs text-slate-500"><?php echo e($merchant->code); ?></div>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($merchant->vendor->business_name ?? '—'); ?></td>
                        <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($merchant->phone_masked ?: '—'); ?></td>
                        <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($merchant->onboarding_status ?: '—'); ?></td>
                        <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($applied[$merchant->id]->entry_count ?? 0); ?></td>
                        <td class="px-5 py-3 text-sm text-slate-900">₹<?php echo e(number_format((float) ($applied[$merchant->id]->total_commission ?? 0), 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-sm text-slate-500">No merchants yet.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4"><?php echo e($merchants->links()); ?></div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/merchants.blade.php ENDPATH**/ ?>