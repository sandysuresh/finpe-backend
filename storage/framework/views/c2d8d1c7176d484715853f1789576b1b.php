<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Vendor Bank Accounts</h1>
        <p class="mt-1 text-sm text-slate-500">Review accounts vendors will use for Add Money.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" class="fi-input w-60 text-sm" placeholder="Search vendor, holder, or bank">
            <select wire:model.live="filterStatus" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <div class="fi-card overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($accounts->isEmpty()): ?>
            <div class="py-16 text-center text-sm text-slate-500">No bank accounts found.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Vendor','Holder','Account','IFSC','Bank','Status','Reason','Action']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-5 py-4 text-sm">
                                    <div class="font-semibold text-slate-800"><?php echo e($account->vendor->business_name); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo e($account->vendor->vendor_code); ?></div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700"><?php echo e($account->account_holder); ?></td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700"><?php echo e($account->account_number); ?></td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700"><?php echo e($account->ifsc_code); ?></td>
                                <td class="px-5 py-4 text-sm text-slate-700"><?php echo e($account->bank_name); ?></td>
                                <td class="px-5 py-4 text-xs font-semibold"><?php echo e(ucfirst($account->status)); ?></td>
                                <td class="max-w-[200px] px-5 py-4 text-xs text-red-600"><?php echo e($account->rejection_reason ?: '—'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($account->status === 'pending'): ?>
                                        <div class="flex gap-2">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth('admin')->user()->hasPermission('wallet-requests', 'approve')): ?>
                                                <button wire:click="openAction(<?php echo e($account->id); ?>, 'approve')" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Approve</button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth('admin')->user()->hasPermission('wallet-requests', 'reject')): ?>
                                                <button wire:click="openAction(<?php echo e($account->id); ?>, 'reject')" class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white">Reject</button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400"><?php echo e($account->reviewed_at?->format('d M Y, h:i A') ?? '—'); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4"><?php echo e($accounts->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal && $actionId): ?>
        <?php $account = $accounts->firstWhere('id', $actionId) ?? \App\Models\VendorBankAccount::with('vendor')->find($actionId); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($account): ?>
            <div class="fi-modal-overlay">
                <div class="fi-modal">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-base font-semibold text-slate-900"><?php echo e($actionType === 'approve' ? 'Approve bank account' : 'Reject bank account'); ?></h3>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($account->vendor->business_name); ?> · <?php echo e($account->bank_name); ?> · <?php echo e($account->account_number); ?> · <?php echo e($account->ifsc_code); ?></p>
                    </div>
                    <div class="p-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actionType === 'reject'): ?>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rejection reason</label>
                            <textarea wire:model="rejectionReason" rows="3" class="fi-input text-sm"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rejectionReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <p class="text-sm text-slate-600">This account will be available in that vendor's Add Money form.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button wire:click="$set('showModal', false)" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold">Cancel</button>
                        <button wire:click="confirm" class="flex-1 rounded-xl py-2.5 text-sm font-semibold text-white <?php echo e($actionType === 'approve' ? 'bg-emerald-600' : 'bg-red-500'); ?>">Confirm</button>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/vendor-bank-accounts.blade.php ENDPATH**/ ?>