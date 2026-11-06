<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900"><?php echo e($meta['title']); ?></h1>
        <p class="mt-1 text-sm text-slate-500">Partners → <?php echo e($meta['title']); ?>. <?php echo e($meta['text']); ?></p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search partner or reference...">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section === 'transactions'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Partner','Provider','Amount','FinPe commission','Total wallet debit','Status','Failure','Time']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif($section === 'wallet'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Partner','Code','Balance','Hold']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif($section === 'commission'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Vendor','Payout Provider','Type','Rate','Commission']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif($section === 'settlement'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Partner','Amount','Net','Status','Settled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Partner','Code','KYC','Comment','Reviewed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section === 'transactions'): ?>
                                <td class="px-5 py-3 text-sm font-medium text-slate-900"><?php echo e($row->reference); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($row->vendor->business_name ?? '—'); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e(($row->type ?? null) === 'payout' ? (\App\Support\CommissionProviders::name($row->payout_provider) ?: '—') : '—'); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ <?php echo e(number_format((float) $row->amount, 2)); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-800"><?php echo e(($row->type ?? null) === 'payout' ? '₹'.number_format((float) ($row->payout_charge ?? 0), 2) : '—'); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-800"><?php echo e(($row->type ?? null) === 'payout' ? '₹'.number_format((float) ($row->total_debited ?? $row->amount), 2) : '—'); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e(ucfirst((string) $row->status)); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-600"><?php echo e($row->failure_reason ?? '—'); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-500"><?php echo e($row->created_at?->format('d M Y, h:i A')); ?></td>
                            <?php elseif($section === 'wallet'): ?>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900"><?php echo e($row->business_name); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-500"><?php echo e($row->vendor_code); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ <?php echo e(number_format((float) ($row->wallet->balance ?? 0), 2)); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700">₹ <?php echo e(number_format((float) ($row->wallet->hold_balance ?? 0), 2)); ?></td>
                            <?php elseif($section === 'commission'): ?>
                                <?php ($rule = $configured[$row->id] ?? null); ?>
                                <td class="px-5 py-3">
                                    <p class="text-sm font-semibold text-slate-900"><?php echo e($row->business_name); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo e($row->vendor_code); ?></p>
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rule && $rule->provider): ?>
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700"><?php echo e(\App\Support\CommissionProviders::name($rule->provider)); ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($rule ? ucfirst((string) $rule->type) : '—'); ?></td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $rule): ?>—<?php elseif($rule->calc_type === 'percentage'): ?><?php echo e(number_format((float) $rule->value, 2)); ?>%<?php else: ?>₹<?php echo e(number_format((float) $rule->value, 2)); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">₹<?php echo e(number_format((float) ($applied[$row->id]->total_commission ?? 0), 2)); ?></td>
                            <?php elseif($section === 'settlement'): ?>
                                <td class="px-5 py-3 text-sm font-medium text-slate-900"><?php echo e($row->reference); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e($row->vendor->business_name ?? '—'); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ <?php echo e(number_format((float) $row->amount, 2)); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-800">₹ <?php echo e(number_format((float) $row->net_amount, 2)); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e(ucfirst((string) $row->status)); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-500"><?php echo e($row->settled_at?->format('d M Y, h:i A') ?: '—'); ?></td>
                            <?php else: ?>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900"><?php echo e($row->business_name); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-500"><?php echo e($row->vendor_code); ?></td>
                                <td class="px-5 py-3 text-sm text-slate-700"><?php echo e(ucfirst((string) $row->kyc_status)); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-600"><?php echo e($row->kyc_comment ?: '—'); ?></td>
                                <td class="px-5 py-3 text-xs text-slate-500"><?php echo e($row->kyc_reviewed_at?->format('d M Y, h:i A') ?: '—'); ?></td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-sm text-slate-500">No records yet.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4"><?php echo e($rows->links()); ?></div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/partner-section.blade.php ENDPATH**/ ?>