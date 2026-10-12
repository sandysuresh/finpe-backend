<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Wallet reports</h1>
        <p class="mt-1 text-sm text-slate-500"><?php echo e($labels['heading']); ?></p>
    </div>

    <?php echo $__env->make('livewire.admin.partials.wallet-report-filters', ['showVendor' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('livewire.admin.partials.wallet-report-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/reports.blade.php ENDPATH**/ ?>