<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Settlement Report</h1>
            <p class="mt-1 text-sm text-slate-500">Track your settlement payouts and status.</p>
        </div>
    </div>

    
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Total Settled</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">₹<?php echo e($summary['total_amount']); ?></p>
        </div>
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Completed</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600"><?php echo e($summary['completed']); ?></p>
        </div>
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Pending</p>
            <p class="mt-3 text-3xl font-bold text-amber-600"><?php echo e($summary['pending']); ?></p>
        </div>
    </div>

    
    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
            <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none">
            <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none">
            <button wire:click="resetFilters" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50">Reset</button>
        </div>
    </div>

    
    <div class="fi-card overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settlements->isEmpty()): ?>
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <p class="mt-3 text-sm font-medium text-slate-700">No settlements yet</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto fi-scroll">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Amount','Fee','Net Amount','Bank','Status','Date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $settlements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $sc = match($s->status) {
                                    'completed'=>'bg-emerald-50 text-emerald-700',
                                    'failed'   =>'bg-red-50 text-red-600',
                                    'processing'=>'bg-blue-50 text-blue-600',
                                    default    =>'bg-amber-50 text-amber-700',
                                };
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs font-semibold text-slate-800"><?php echo e($s->reference); ?></td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs text-slate-700">₹<?php echo e(number_format((float)$s->amount,2)); ?></td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs text-red-500">-₹<?php echo e(number_format((float)$s->fee,2)); ?></td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs font-bold text-slate-900">₹<?php echo e(number_format((float)$s->net_amount,2)); ?></td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs text-slate-500"><?php echo e($s->bank_name ?? '—'); ?></td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($sc); ?>"><?php echo e(ucfirst($s->status)); ?></span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs text-slate-400"><?php echo e($s->created_at->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4"><?php echo e($settlements->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/settlement-report.blade.php ENDPATH**/ ?>