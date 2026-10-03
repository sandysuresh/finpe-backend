<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Audit Logs</h1>
        <p class="mt-1 text-sm text-slate-500">Administration → Audit Logs. Who changed what, with the previous value, new value, time, and IP address.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" name="audit_log_filter" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search name, action, subject, or IP...">
            <select wire:model.live="filterActor" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none">
                <option value="">Admin and user</option>
                <option value="admin">Admin</option>
                <option value="vendor">User</option>
            </select>
            <select wire:model.live="filterStatus" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none">
                <option value="">All statuses</option>
                <option value="Success">Success</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
                <option value="Failed">Failed</option>
            </select>
        </div>
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Who','Action','User / Transaction','Old value','New value','Date / time','IP address','Status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr wire:key="audit-log-<?php echo e($log->id); ?>" class="align-top hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <div class="font-semibold text-slate-900"><?php echo e($log->actor_name); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($log->actor_email ?: '—'); ?></div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-800"><?php echo e($log->action); ?></td>
                            <td class="px-4 py-3 text-sm text-slate-700"><?php echo e($log->subject_label ?: '—'); ?></td>
                            <td class="max-w-xs px-4 py-3 text-xs leading-5 text-slate-600"><?php echo e($log->old_value ?: '—'); ?></td>
                            <td class="max-w-xs px-4 py-3 text-xs leading-5 text-slate-800"><?php echo e($log->new_value ?: '—'); ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"><?php echo e($log->created_at?->timezone(config('app.timezone'))->format('d-M-Y H:i')); ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"><?php echo e($log->ip_address ?: '—'); ?></td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <?php
                                    $tone = match($log->status) {
                                        'Approved', 'Success' => 'bg-emerald-50 text-emerald-700',
                                        'Rejected', 'Failed' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                ?>
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($tone); ?>"><?php echo e($log->status); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center text-sm text-slate-500">No audit logs yet.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4"><?php echo e($logs->links()); ?></div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/audit-logs.blade.php ENDPATH**/ ?>