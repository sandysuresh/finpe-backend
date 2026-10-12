<div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-6">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
        [$labels['opening'], $summary['opening'], 'text-slate-900'],
        ['Total credit', $summary['credit'], 'text-emerald-700'],
        ['Total debit', $summary['debit'], 'text-red-700'],
        ['Refunds', $summary['refunds'], 'text-amber-700'],
        ['Transaction count', $summary['count'], 'text-slate-900'],
        [$labels['closing'], $summary['closing'], 'text-blue-800'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="fi-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400"><?php echo e($label); ?></p>
            <p class="mt-1 text-xl font-bold <?php echo e($color); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label === 'Transaction count'): ?>
                    <?php echo e(number_format($value)); ?>

                <?php else: ?>
                    ₹<?php echo e(number_format($value, 2)); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<p class="mb-5 text-sm text-slate-500"><?php echo e($note); ?></p>

<div class="fi-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Date/time','Vendor','Reference','Type','Payout provider','Description','Credit','Debit','Running balance','Status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"><?php echo e($line['at']?->timezone(config('app.timezone'))->format('d M Y H:i:s')); ?></td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($line['vendor_name']); ?></p>
                            <p class="font-mono text-xs text-slate-500"><?php echo e($line['vendor_code']); ?> · <?php echo e($line['vendor_id']); ?></p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-700">
                            <?php echo e($line['reference'] !== '' ? $line['reference'] : '—'); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($line['txn_reference'] !== '' && $line['txn_reference'] !== $line['reference']): ?>
                                <p class="text-slate-400"><?php echo e($line['txn_reference']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-xs font-semibold text-slate-700"><?php echo e($line['entry']); ?></td>
                        <td class="px-4 py-3 text-xs text-slate-600"><?php echo e($line['provider'] !== '' ? $line['provider'] : '—'); ?></td>
                        <td class="max-w-[220px] px-4 py-3 text-xs text-slate-600"><?php echo e($line['description'] !== '' ? $line['description'] : '—'); ?></td>
                        <td class="px-4 py-3 text-sm text-emerald-700"><?php echo e($line['credit'] > 0 ? '₹'.number_format($line['credit'], 2) : '—'); ?></td>
                        <td class="px-4 py-3 text-sm text-red-700"><?php echo e($line['debit'] > 0 ? '₹'.number_format($line['debit'], 2) : '—'); ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-900">₹<?php echo e(number_format($line['running'], 2)); ?></td>
                        <td class="px-4 py-3 text-xs text-slate-600"><?php echo e($line['txn_status'] !== '' ? ucfirst($line['txn_status']) : '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-sm text-slate-500">No ledger rows match these filters.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lines->hasPages()): ?>
        <div class="border-t border-slate-100 px-5 py-4"><?php echo e($lines->links()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/partials/wallet-report-table.blade.php ENDPATH**/ ?>