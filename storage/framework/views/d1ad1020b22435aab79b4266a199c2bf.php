<div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wallet report</h1>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($report['name']); ?> · <span class="font-mono"><?php echo e($report['code']); ?></span></p>
        </div>
        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Sample data</span>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['Opening balance', $report['opening'], 'text-slate-900'],
            ['Credit', $report['credit'], 'text-emerald-700'],
            ['Debit', $report['debit'], 'text-red-700'],
            ['Closing balance', $report['closing'], 'text-violet-800'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="fi-card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400"><?php echo e($label); ?></p>
                <p class="mt-2 text-2xl font-bold <?php echo e($color); ?>">₹<?php echo e(number_format($value, 2)); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px;">
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
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-primary">Export Excel</button>
            </div>
        </div>
    </div>

    <p class="mb-5 text-sm text-slate-500">Closing balance = opening + credit − debit.</p>

    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Ledger</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Date','Reference','Description','Type','Amount','Balance before','Balance after']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $report['lines']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-5 py-3 text-sm text-slate-600"><?php echo e(\Illuminate\Support\Carbon::parse($line['date'])->format('d M Y')); ?></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-700"><?php echo e($line['reference']); ?></td>
                            <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($line['description']); ?></td>
                            <td class="px-5 py-3 text-xs font-semibold <?php echo e($line['type'] === 'credit' ? 'text-emerald-700' : 'text-red-700'); ?>"><?php echo e(ucfirst($line['type'])); ?></td>
                            <td class="px-5 py-3 text-sm">₹<?php echo e(number_format($line['amount'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm text-slate-600">₹<?php echo e(number_format($line['balance_before'], 2)); ?></td>
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">₹<?php echo e(number_format($line['balance_after'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-500">No ledger lines in this date range.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/reports.blade.php ENDPATH**/ ?>