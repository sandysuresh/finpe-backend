<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">AePS Transactions</h1>
        <p class="mt-1 text-sm text-slate-500">Read-only AePS transactions. This screen does not change wallets or commission.</p>
    </div>

    <div class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-3 xl:grid-cols-6">
        <select wire:model.live="vendorId" class="fi-input text-sm">
            <option value="">Vendor</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->business_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <select wire:model.live="merchantId" class="fi-input text-sm">
            <option value="">Merchant</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $merchants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $merchant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($merchant->id); ?>"><?php echo e($merchant->code); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <select wire:model.live="service" class="fi-input text-sm">
            <option value="">Service</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($service); ?>"><?php echo e($this->serviceLabel($service)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <select wire:model.live="status" class="fi-input text-sm">
            <option value="">Status</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status); ?>"><?php echo e(str_replace('_', ' ', $status)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <input wire:model.live="dateFrom" type="date" class="fi-input text-sm" aria-label="From date">
        <input wire:model.live="dateTo" type="date" class="fi-input text-sm" aria-label="To date">
    </div>

    <div class="fi-card overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Vendor','Merchant','Service','Amount','FinPe reference','Provider reference / RRN','Status','Provider status','Created']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="cursor-pointer hover:bg-slate-50" wire:click="show(<?php echo e($txn->id); ?>)">
                        <td class="px-3 py-3 text-sm text-slate-700"><?php echo e($txn->vendor->business_name ?? '—'); ?></td>
                        <td class="px-3 py-3 text-sm">
                            <div class="font-semibold text-slate-900"><?php echo e($txn->merchant->code ?? '—'); ?></div>
                            <div class="text-xs text-slate-500"><?php echo e(trim(($txn->merchant->first_name ?? '').' '.($txn->merchant->last_name ?? ''))); ?></div>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-700"><?php echo e($this->serviceLabel($txn->service)); ?></td>
                        <td class="px-3 py-3 text-sm text-slate-900">₹<?php echo e(number_format((float) $txn->amount, 2)); ?></td>
                        <td class="px-3 py-3 text-sm font-semibold text-blue-700"><?php echo e($txn->reference); ?></td>
                        <td class="px-3 py-3 text-xs text-slate-700">
                            <div><?php echo e($txn->provider_txn_ref ?: '—'); ?></div>
                            <div><?php echo e($txn->rrn ?: '—'); ?></div>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-700"><?php echo e(str_replace('_', ' ', (string) $txn->status)); ?></td>
                        <td class="px-3 py-3 text-sm text-slate-700"><?php echo e($txn->provider_status_code ?: '—'); ?></td>
                        <td class="px-3 py-3 text-xs text-slate-600"><?php echo e($txn->created_at?->format('d M Y, h:i A')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-5 py-16 text-center text-sm text-slate-500">No AePS transactions yet.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4"><?php echo e($transactions->links()); ?></div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail): ?>
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4" wire:click.self="$set('detailId', null)">
            <div class="fi-card max-h-[85vh] w-full max-w-2xl overflow-y-auto p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-bold text-slate-900"><?php echo e($detail->reference); ?></h2>
                    <div class="flex items-center gap-4">
                        <a href="<?php echo e(route('admin.aeps-transactions.receipt', $detail->reference)); ?>" class="text-sm font-semibold text-blue-700">Download Receipt PDF</a>
                        <button type="button" wire:click="$set('detailId', null)" class="text-sm font-semibold text-slate-500">Close</button>
                    </div>
                </div>
                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <div class="md:col-span-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Transaction Details</div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Service</dt><dd><?php echo e($this->serviceLabel($detail->service)); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd>₹<?php echo e(number_format((float) $detail->amount, 2)); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">FinPe reference</dt><dd><?php echo e($detail->reference); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Aadhaar</dt><dd><?php echo e(\App\Services\Aeps\AepsPayloadSanitizer::displayAadhaar($detail->aadhaar_masked)); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><?php echo e(str_replace('_', ' ', (string) $detail->status)); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Created</dt><dd><?php echo e($detail->created_at?->format('d M Y, h:i A')); ?></dd></div>
                    <div class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Merchant and vendor</div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Vendor</dt><dd><?php echo e($detail->vendor->business_name ?? '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Merchant</dt><dd><?php echo e($detail->merchant->code ?? '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Client reference</dt><dd><?php echo e($detail->client_reference); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Provider reference</dt><dd class="text-right"><?php echo e($detail->provider_txn_ref ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">RRN</dt><dd><?php echo e($detail->rrn ?: '—'); ?></dd></div>
                    <div class="md:col-span-2 mt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Provider details</div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Provider status</dt><dd><?php echo e($detail->provider_status_code ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Provider message</dt><dd class="text-right"><?php echo e($detail->provider_status_description ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">NPCI</dt><dd class="text-right"><?php echo e(trim(($detail->npci_code ?: '').' '.($detail->npci_message ?: '')) ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Bank IIN</dt><dd><?php echo e($detail->bank_iin ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Failure reason</dt><dd class="text-right"><?php echo e($detail->failure_reason ?: '—'); ?></dd></div>
                </dl>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/aeps-transactions.blade.php ENDPATH**/ ?>