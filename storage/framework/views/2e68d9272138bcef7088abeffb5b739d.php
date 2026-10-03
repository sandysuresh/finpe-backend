<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">API Logs</h1>
        <p class="mt-1 text-sm text-slate-500">Bank & API → API Logs. Vendor API calls recorded by the gateway.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search vendor, endpoint, or IP...">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Vendor','Method','Endpoint','Status','IP address','Time','Duration']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr wire:key="api-log-<?php echo e($log->id); ?>" class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <div class="font-semibold text-slate-900"><?php echo e($log->vendor->business_name ?? '—'); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($log->vendor->vendor_code ?? ''); ?></div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-800"><?php echo e($log->method); ?></td>
                            <td class="px-4 py-3 text-sm text-slate-700"><?php echo e($log->endpoint); ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm"><?php echo e($log->status_code); ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"><?php echo e($log->ip_address ?: '—'); ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"><?php echo e($log->created_at?->timezone(config('app.timezone'))->format('d-M-Y H:i')); ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"><?php echo e($log->response_time_ms !== null ? $log->response_time_ms.' ms' : '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-sm text-slate-500">No API logs yet.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4"><?php echo e($logs->links()); ?></div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/api-logs.blade.php ENDPATH**/ ?>