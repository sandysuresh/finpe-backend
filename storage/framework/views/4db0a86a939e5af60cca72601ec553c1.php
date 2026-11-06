<?php ($processed = $selected->processedAt()); ?>
<dl class="grid gap-3 text-sm sm:grid-cols-2">
    <div><dt class="text-slate-500">FinPe transaction ID</dt><dd class="font-medium"><?php echo e($selected->id); ?></dd></div>
    <div><dt class="text-slate-500">AlliancePe payout ID</dt><dd><?php echo e($selected->provider_payout_id ?: '—'); ?></dd></div>
    <div><dt class="text-slate-500">Merchant order ID</dt><dd><?php echo e($selected->merchant_order_id); ?></dd></div>
    <div><dt class="text-slate-500">Amount</dt><dd>₹<?php echo e(number_format((float) $selected->amount, 2)); ?></dd></div>
    <div><dt class="text-slate-500">FinPe commission</dt><dd><?php echo e($selected->commission_type ? $selected->commission_type.' '.$selected->commission_rate : '—'); ?> · ₹<?php echo e(number_format((float) $selected->commission_amount, 2)); ?></dd></div>
    <div><dt class="text-slate-500">Total wallet debit</dt><dd>₹<?php echo e(number_format((float) ($selected->total_debited ?? $selected->amount), 2)); ?></dd></div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($showVendor)): ?>
        <div><dt class="text-slate-500">Vendor</dt><dd><?php echo e($selected->vendor?->business_name ?: '—'); ?> (<?php echo e($selected->vendor_id); ?>)</dd></div>
        <div><dt class="text-slate-500">Environment</dt><dd><?php echo e($selected->environment ? strtoupper($selected->environment) : '—'); ?></dd></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div><dt class="text-slate-500">Beneficiary</dt><dd><?php echo e($selected->beneficiary_name); ?></dd></div>
    <div><dt class="text-slate-500">Bank</dt><dd><?php echo e($selected->bank_name); ?></dd></div>
    <div><dt class="text-slate-500">Account</dt><dd><?php echo e($selected->maskedAccount()); ?></dd></div>
    <div><dt class="text-slate-500">IFSC</dt><dd><?php echo e($selected->ifsc_code); ?></dd></div>
    <div><dt class="text-slate-500">Phone</dt><dd><?php echo e($selected->maskedPhone()); ?></dd></div>
    <div><dt class="text-slate-500">Email</dt><dd><?php echo e($selected->maskedEmail()); ?></dd></div>
    <div><dt class="text-slate-500">Mode</dt><dd><?php echo e(strtoupper($selected->transfer_mode)); ?></dd></div>
    <div><dt class="text-slate-500">Payout status</dt><dd class="font-medium"><?php echo e($selected->status); ?></dd></div>
    <div><dt class="text-slate-500">Wallet status</dt><dd><?php echo e($selected->wallet_state); ?></dd></div>
    <div><dt class="text-slate-500">UTR</dt><dd><?php echo e($selected->utr_number ?: '—'); ?></dd></div>
    <div class="sm:col-span-2"><dt class="text-slate-500">Failure reason</dt><dd><?php echo e($selected->failure_reason ?: '—'); ?></dd></div>
    <div><dt class="text-slate-500">Created</dt><dd><?php echo e($selected->created_at?->format('d M Y H:i:s')); ?></dd></div>
    <div><dt class="text-slate-500">Updated</dt><dd><?php echo e($selected->updated_at?->format('d M Y H:i:s')); ?></dd></div>
    <div><dt class="text-slate-500">Processed</dt><dd><?php echo e($processed?->format('d M Y H:i:s') ?: '—'); ?></dd></div>
    <div><dt class="text-slate-500">Refunded</dt><dd><?php echo e($selected->refunded_at?->format('d M Y H:i:s') ?: '—'); ?></dd></div>
</dl>
<div class="mt-4">
    <h3 class="text-sm font-semibold text-slate-900">Status timeline</h3>
    <ol class="mt-2 space-y-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                <span class="font-medium"><?php echo e($event->status); ?></span>
                <span class="text-slate-500">· wallet <?php echo e($event->wallet_state); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->utr_number): ?><span class="text-slate-700">· UTR <?php echo e($event->utr_number); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="text-xs text-slate-500"><?php echo e($event->created_at?->format('d M Y H:i:s')); ?> · <?php echo e($event->source); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->failure_reason): ?><div class="text-xs text-rose-700"><?php echo e($event->failure_reason); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="text-sm text-slate-500">No status events recorded.</li>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </ol>
</div>
<div class="mt-4">
    <h3 class="text-sm font-semibold text-slate-900">Wallet ledger</h3>
    <ul class="mt-2 space-y-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ledger; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                <?php echo e(strtoupper($entry->type)); ?> ₹<?php echo e(number_format((float) $entry->amount, 2)); ?>

                · reference <?php echo e($entry->reference); ?>

                <div class="text-xs text-slate-500"><?php echo e($entry->description); ?> · balance ₹<?php echo e(number_format((float) $entry->balance_after, 2)); ?></div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="text-sm text-slate-500">No ledger entries for this reference.</li>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </ul>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/alliancepe/payout-detail.blade.php ENDPATH**/ ?>