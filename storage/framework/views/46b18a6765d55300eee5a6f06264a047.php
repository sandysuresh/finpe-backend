<div class="mx-auto max-w-7xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">AlliancePe payouts</h1>
            <p class="text-sm text-slate-500">Provider wallet and persisted payout history. This is separate from VimoPay.</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($environment === 'LIVE' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-900'); ?>">Configured <?php echo e($environment); ?></span>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice): ?><div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e($notice); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?><div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800"><?php echo e($error); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <button type="button" wire:click="checkConnection" wire:loading.attr="disabled" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">Check connection</button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($balance): ?>
            <div class="text-sm text-slate-700">Provider balance: <span class="font-semibold"><?php echo e($balance['currency'] ?? 'INR'); ?> <?php echo e($balance['available'] ?? '—'); ?></span></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <section class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-semibold text-slate-900">Transaction history</h2>
            <button type="button" wire:click="export" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">Export CSV</button>
        </div>
        <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-sm">Search reference<input wire:model.live.debounce.300ms="search" class="fi-input mt-1 w-full" placeholder="Order, payout ID, or transaction ID"></label>
            <label class="text-sm">Vendor
                <select wire:model.live="vendorId" class="fi-input mt-1 w-full">
                    <option value="">All vendors</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->business_name); ?> (<?php echo e($vendor->id); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </label>
            <label class="text-sm">Transaction ID<input wire:model.live.debounce.300ms="transactionId" class="fi-input mt-1 w-full"></label>
            <label class="text-sm">Merchant order ID<input wire:model.live.debounce.300ms="merchantOrderId" class="fi-input mt-1 w-full"></label>
            <label class="text-sm">Payout ID<input wire:model.live.debounce.300ms="payoutId" class="fi-input mt-1 w-full"></label>
            <label class="text-sm">Status
                <select wire:model.live="status" class="fi-input mt-1 w-full">
                    <option value="">All</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['pending','processing','completed','failed','cancelled','on_hold','rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($state); ?>"><?php echo e($state); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </label>
            <label class="text-sm">Mode
                <select wire:model.live="transferMode" class="fi-input mt-1 w-full">
                    <option value="">All</option>
                    <option value="imps">IMPS</option>
                    <option value="neft">NEFT</option>
                    <option value="rtgs">RTGS</option>
                </select>
            </label>
            <label class="text-sm">Environment
                <select wire:model.live="environmentFilter" class="fi-input mt-1 w-full">
                    <option value="">All</option>
                    <option value="uat">UAT</option>
                    <option value="live">LIVE</option>
                </select>
            </label>
            <label class="text-sm">From<input wire:model.live="dateFrom" type="date" class="fi-input mt-1 w-full"></label>
            <label class="text-sm">To<input wire:model.live="dateTo" type="date" class="fi-input mt-1 w-full"></label>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Payout ID</th>
                        <th class="px-3 py-2">Merchant order</th>
                        <th class="px-3 py-2">Vendor</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2">Commission</th>
                        <th class="px-3 py-2">Total debited</th>
                        <th class="px-3 py-2">Beneficiary</th>
                        <th class="px-3 py-2">Account</th>
                        <th class="px-3 py-2">Bank</th>
                        <th class="px-3 py-2">IFSC</th>
                        <th class="px-3 py-2">Mode</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">UTR</th>
                        <th class="px-3 py-2">Failure</th>
                        <th class="px-3 py-2">Env</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr wire:click="show(<?php echo e($row->id); ?>)" class="cursor-pointer border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-3 py-2"><?php echo e($row->id); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->provider_payout_id ?: '—'); ?></td>
                        <td class="px-3 py-2 font-medium"><?php echo e($row->merchant_order_id); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->vendor?->business_name ?: '—'); ?> <span class="text-slate-500">#<?php echo e($row->vendor_id); ?></span></td>
                        <td class="px-3 py-2">₹<?php echo e(number_format((float) $row->amount, 2)); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->commission_type ? $row->commission_type.' '.$row->commission_rate : '—'); ?> · ₹<?php echo e(number_format((float) $row->commission_amount, 2)); ?></td>
                        <td class="px-3 py-2">₹<?php echo e(number_format((float) ($row->total_debited ?? $row->amount), 2)); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->beneficiary_name); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->maskedAccount()); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->bank_name); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->ifsc_code); ?></td>
                        <td class="px-3 py-2"><?php echo e(strtoupper($row->transfer_mode)); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->created_at?->format('d M Y H:i')); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->status); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->utr_number ?: '—'); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->failure_reason ?: '—'); ?></td>
                        <td class="px-3 py-2"><?php echo e($row->environment ? strtoupper($row->environment) : '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="17" class="px-3 py-6 text-center text-slate-500">No AlliancePe payouts match these filters.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3"><?php echo e($payouts->links()); ?></div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected): ?>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Transaction <?php echo e($selected->id); ?></h2>
                <button type="button" wire:click="refreshSelected" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold">Refresh status</button>
            </div>
            <?php echo $__env->make('livewire.alliancepe.payout-detail', ['showVendor' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <p class="text-sm text-slate-500">Select a transaction to see its lifecycle, UTR, and wallet ledger reference.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/alliancepe-payouts.blade.php ENDPATH**/ ?>