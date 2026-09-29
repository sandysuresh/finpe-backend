<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Commission History</h1>
            <p class="mt-1 text-sm text-slate-500">Read-only snapshots. Amounts stay as recorded even if the rule changes later.</p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canExport): ?>
            <button type="button" wire:click="export" class="fi-btn">Export</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-4">
        <select wire:model.live="vendorId" class="fi-input text-sm"><option value="">Vendor</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->business_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <select wire:model.live="merchantId" class="fi-input text-sm"><option value="">Merchant</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $merchants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $merchant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($merchant->id); ?>"><?php echo e($merchant->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select>
        <select wire:model.live="service" class="fi-input text-sm"><option value="">Service</option><option value="imps">IMPS</option><option value="neft">NEFT</option><option value="rtgs">RTGS</option></select>
        <select wire:model.live="sourceType" class="fi-input text-sm"><option value="">Source type</option><option value="transactions">Payout</option></select>
        <select wire:model.live="status" class="fi-input text-sm"><option value="">Status</option><option value="recorded">Recorded</option></select>
        <input wire:model.live="dateFrom" type="date" class="fi-input text-sm">
        <input wire:model.live="dateTo" type="date" class="fi-input text-sm">
    </div>

    <div class="fi-card overflow-x-auto">
        <table class="min-w-full">
            <thead><tr><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Partner','Merchant','Service','Transaction date','Base amount','Calculation','Rate','Commission','Status','Commission date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="cursor-pointer" wire:click="show(<?php echo e($entry->id); ?>)">
                        <td class="px-3 py-3 text-sm"><?php echo e($entry->source_reference ?: '—'); ?></td>
                        <td class="px-3 py-3 text-sm"><?php echo e($entry->vendor_name_snapshot ?: '—'); ?></td>
                        <td class="px-3 py-3 text-sm"><?php echo e($entry->merchant_id ? ($entry->merchant_code_snapshot ?: 'Merchant') : '—'); ?></td>
                        <td class="px-3 py-3 text-sm"><?php echo e($entry->service_snapshot ?: '—'); ?></td>
                        <td class="px-3 py-3 text-xs"><?php echo e($entry->source_type === 'transactions' ? ($entry->sourceTransaction?->created_at?->format('d M Y, h:i A') ?? '—') : '—'); ?></td>
                        <td class="px-3 py-3 text-sm">₹<?php echo e(number_format((float) $entry->base_amount, 2)); ?></td>
                        <td class="px-3 py-3 text-sm"><?php echo e(ucfirst((string) $entry->calc_type)); ?></td>
                        <td class="px-3 py-3 text-sm"><?php echo e($entry->calc_type === 'percentage' ? number_format((float) $entry->rate_value, 2).'%' : '₹'.number_format((float) $entry->rate_value, 2)); ?></td>
                        <td class="px-3 py-3 text-sm font-semibold">₹<?php echo e(number_format((float) $entry->commission_amount, 2)); ?></td>
                        <td class="px-3 py-3 text-sm"><?php echo e(ucfirst((string) $entry->status)); ?></td>
                        <td class="px-3 py-3 text-xs"><?php echo e($entry->created_at?->format('d M Y, h:i A')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="11" class="px-4 py-8 text-center text-sm text-slate-500">No commission entries.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($entries->links()); ?></div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail): ?>
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4" wire:click.self="$set('detailId', null)">
            <div class="fi-card w-full max-w-lg p-6 text-sm">
                <h2 class="text-lg font-bold">Commission snapshot</h2>
                <dl class="mt-4 space-y-2">
                    <div class="flex justify-between"><dt>Reference</dt><dd><?php echo e($detail->source_reference ?: '—'); ?></dd></div>
                    <div class="flex justify-between"><dt>Partner</dt><dd><?php echo e($detail->vendor_name_snapshot ?: '—'); ?></dd></div>
                    <div class="flex justify-between"><dt>Merchant</dt><dd><?php echo e($detail->merchant_id ? ($detail->merchant_code_snapshot ?: 'Merchant') : '—'); ?></dd></div>
                    <div class="flex justify-between"><dt>Base amount</dt><dd>₹<?php echo e(number_format((float) $detail->base_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt>Rate</dt><dd><?php echo e($detail->calc_type === 'percentage' ? number_format((float) $detail->rate_value, 2).'%' : '₹'.number_format((float) $detail->rate_value, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt>Commission</dt><dd>₹<?php echo e(number_format((float) $detail->commission_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt>Status</dt><dd><?php echo e(ucfirst((string) $detail->status)); ?></dd></div>
                    <div class="flex justify-between"><dt>Commission date</dt><dd><?php echo e($detail->created_at?->format('d M Y, h:i A')); ?></dd></div>
                </dl>
                <p class="mt-4 text-xs text-slate-500">This snapshot is not editable. Refund and reversal accounting is pending a business rule.</p>
                <button type="button" wire:click="$set('detailId', null)" class="fi-btn mt-4">Close</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/commission-history.blade.php ENDPATH**/ ?>