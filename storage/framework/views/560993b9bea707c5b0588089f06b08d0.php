<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Merchant Commission</h1>
        <p class="mt-1 text-sm text-slate-500">Applied commission for entries that include a merchant. Payouts without a merchant stay under Agent Commission.</p>
    </div>
    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-4">
        <select wire:model.live="vendorId" class="fi-input text-sm"><option value="">Vendor</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->business_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <select wire:model.live="merchantId" class="fi-input text-sm"><option value="">Merchant</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $merchantOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $merchant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($merchant->id); ?>"><?php echo e($merchant->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <input wire:model.live="dateFrom" type="date" class="fi-input text-sm">
        <input wire:model.live="dateTo" type="date" class="fi-input text-sm">
    </div>
    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead><tr><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Merchant','Code','Partner','Commission entry count','Total commission']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php ($merchant = $merchants[$row->merchant_id] ?? null); ?>
                    <tr>
                        <td class="px-5 py-3 text-sm font-semibold"><a class="text-blue-700" href="<?php echo e(route('admin.commission.history', ['merchant' => $row->merchant_id])); ?>"><?php echo e(trim(($merchant->first_name ?? '').' '.($merchant->last_name ?? '')) ?: 'Merchant'); ?></a></td>
                        <td class="px-5 py-3 text-sm"><?php echo e($merchant->code ?? '—'); ?></td>
                        <td class="px-5 py-3 text-sm"><?php echo e($vendorNames[$row->vendor_id] ?? '—'); ?></td>
                        <td class="px-5 py-3 text-sm"><?php echo e($row->entry_count); ?></td>
                        <td class="px-5 py-3 text-sm">₹<?php echo e(number_format((float) $row->total_commission, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">No merchant commission entries.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($merchantId !== ''): ?>
        <div class="mt-6">
            <h2 class="mb-3 text-lg font-bold text-slate-900">Applied entries</h2>
            <div class="fi-card overflow-x-auto">
                <table class="min-w-full">
                    <thead><tr><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Transaction date','Partner','Merchant','Service','Amount','Rate','Commission','Status','Commission date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-3 py-3 text-sm"><?php echo e($entry->source_reference ?: '—'); ?></td>
                                <td class="px-3 py-3 text-xs"><?php echo e($entry->source_type === 'transactions' ? ($entry->sourceTransaction?->created_at?->format('d M Y, h:i A') ?? '—') : '—'); ?></td>
                                <td class="px-3 py-3 text-sm"><?php echo e($entry->vendor_name_snapshot ?: '—'); ?></td>
                                <td class="px-3 py-3 text-sm"><?php echo e($entry->merchant_code_snapshot ?: ($entry->merchant->code ?? '—')); ?></td>
                                <td class="px-3 py-3 text-sm"><?php echo e($entry->service_snapshot ?: '—'); ?></td>
                                <td class="px-3 py-3 text-sm">₹<?php echo e(number_format((float) $entry->base_amount, 2)); ?></td>
                                <td class="px-3 py-3 text-sm"><?php echo e($entry->calc_type === 'percentage' ? number_format((float) $entry->rate_value, 2).'%' : '₹'.number_format((float) $entry->rate_value, 2)); ?></td>
                                <td class="px-3 py-3 text-sm font-semibold">₹<?php echo e(number_format((float) $entry->commission_amount, 2)); ?></td>
                                <td class="px-3 py-3 text-sm"><?php echo e(ucfirst((string) $entry->status)); ?></td>
                                <td class="px-3 py-3 text-xs"><?php echo e($entry->created_at?->format('d M Y, h:i A')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">No commission entries for this merchant.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/merchant-commission.blade.php ENDPATH**/ ?>