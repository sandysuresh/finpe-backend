<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wallet reports</h1>
            <p class="mt-1 text-sm text-slate-500">Opening, credit, debit and closing balance. Closing = opening + credit − debit.</p>
        </div>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Sample data</span>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['Opening', $summary['opening'], 'text-slate-900'],
            ['Credit', $summary['credit'], 'text-emerald-700'],
            ['Debit', $summary['debit'], 'text-red-700'],
            ['Closing', $summary['closing'], 'text-blue-800'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="fi-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400"><?php echo e($label); ?></p>
                <p class="mt-1 text-xl font-bold <?php echo e($color); ?>">₹<?php echo e(number_format($value, 2)); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px;">
            <div style="width:260px; max-width:100%;">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Vendor</label>
                <select wire:model.live="vendor" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                    <option value="">All vendors</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item['code']); ?>"><?php echo e($item['name']); ?> (<?php echo e($item['code']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">From</label>
                <input type="date" wire:model.live="dateFrom" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">To</label>
                <input type="date" wire:model.live="dateTo" class="fi-input text-sm" style="height:40px; width:168px; padding:0 10px;">
            </div>
            <div style="display:flex; gap:8px; margin-left:auto;">
                <button type="button" wire:click="resetFilters" class="fi-btn fi-btn-secondary">Reset</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth('admin')->user()->hasPermission('reports', 'export')): ?>
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-primary">Export Excel</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="fi-card mb-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Vendor','Opening','Credit','Debit','Closing','']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-5 py-3">
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($row['name']); ?></p>
                                <p class="font-mono text-xs text-slate-500"><?php echo e($row['code']); ?></p>
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-800">₹<?php echo e(number_format($row['opening'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-semibold text-emerald-700">₹<?php echo e(number_format($row['credit'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-semibold text-red-700">₹<?php echo e(number_format($row['debit'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-bold text-slate-900">₹<?php echo e(number_format($row['closing'], 2)); ?></td>
                            <td class="px-5 py-3 text-right">
                                <a href="<?php echo e(route('admin.reports.show', ['report' => $row['token'], 'from' => $dateFrom, 'to' => $dateTo])); ?>" class="inline-flex rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No movement in this date range.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->isNotEmpty()): ?>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">Total</td>
                            <td class="px-5 py-3 text-sm font-semibold">₹<?php echo e(number_format($summary['opening'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-semibold text-emerald-700">₹<?php echo e(number_format($summary['credit'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-semibold text-red-700">₹<?php echo e(number_format($summary['debit'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-bold">₹<?php echo e(number_format($summary['closing'], 2)); ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </table>
        </div>
    </div>

</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/reports.blade.php ENDPATH**/ ?>