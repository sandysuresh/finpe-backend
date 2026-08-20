<div>

    
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wallet Top-up Requests</h1>
            <p class="mt-1 text-sm text-slate-500">Review, approve or reject vendor add money requests.</p>
        </div>
    </div>

    
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Pending</p>
            <p class="mt-3 text-3xl font-bold text-amber-700"><?php echo e($summary['pending']); ?></p>
            <p class="mt-1 text-xs text-slate-400">Awaiting action</p>
        </div>
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Approved</p>
            <p class="mt-3 text-3xl font-bold text-emerald-700"><?php echo e($summary['approved']); ?></p>
            <p class="mt-1 text-xs text-slate-400">This month</p>
        </div>
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Rejected</p>
            <p class="mt-3 text-3xl font-bold text-red-600"><?php echo e($summary['rejected']); ?></p>
            <p class="mt-1 text-xs text-slate-400">Declined</p>
        </div>
        <div class="fi-card p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Total Credited</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">₹<?php echo e(number_format((float)$summary['total_credited'],2)); ?></p>
            <p class="mt-1 text-xs text-slate-400">All time approved</p>
        </div>
    </div>

    
    <div class="fi-card mb-5 px-5 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text"
                   class="fi-input w-60 text-sm" placeholder="Search vendor name or code...">
            <select wire:model.live="filterStatus"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-violet-400">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    
    <div class="fi-card overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requests->isEmpty()): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-slate-700">No requests found</h3>
                <p class="mt-1 text-xs text-slate-400">Vendor wallet top-up requests will appear here.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Vendor','Amount','Mode','UTR / Ref','Bank','Remarks','Status','Date','Action']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 last:text-center"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $sc = match($req->status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-red-50 text-red-600',
                                    default    => 'bg-amber-50 text-amber-700',
                                };
                            ?>
                            <tr id="wallet-request-<?php echo e($req->id); ?>" class="transition-colors hover:bg-slate-50 <?php echo e($highlightId === $req->id ? 'bg-blue-50' : ''); ?>">
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-slate-800"><?php echo e($req->reference); ?></td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-800"><?php echo e($req->vendor->business_name); ?></p>
                                    <p class="text-xs text-slate-400"><?php echo e($req->vendor->vendor_code); ?></p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900">₹<?php echo e(number_format((float)$req->amount,2)); ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600"><?php echo e(ucwords(str_replace('_',' ',$req->payment_mode))); ?></td>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-500"><?php echo e($req->transaction_ref ?? '—'); ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500"><?php echo e($req->bank_name ?? '—'); ?></td>
                                <td class="max-w-[150px] truncate px-5 py-4 text-xs text-slate-500" title="<?php echo e($req->remarks); ?>"><?php echo e($req->remarks ?? '—'); ?></td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($sc); ?>">
                                        <?php echo e(ucfirst($req->status)); ?>

                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($req->admin_note): ?>
                                        <p class="mt-1 max-w-[120px] truncate text-[10px] text-slate-400" title="<?php echo e($req->admin_note); ?>"><?php echo e($req->admin_note); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-400">
                                    <?php echo e($req->created_at->format('d M Y')); ?><br>
                                    <span class="text-[10px]"><?php echo e($req->created_at->format('h:i A')); ?></span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($req->status === 'pending'): ?>
                                        <div class="flex items-center justify-center gap-2">
                                            <button wire:click="openAction(<?php echo e($req->id); ?>,'approve')"
                                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                Approve
                                            </button>
                                            <button wire:click="openAction(<?php echo e($req->id); ?>,'reject')"
                                                    class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">
                                                Reject
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">
                                            <?php echo e($req->actioned_at?->format('d M, h:i A') ?? '—'); ?><br>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($req->approvedBy): ?>
                                                <span class="text-[10px]">by <?php echo e($req->approvedBy->name); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4"><?php echo e($requests->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>


    
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showApproveModal && $actionId): ?>
        <?php $req = $requests->firstWhere('id', $actionId) ?? \App\Models\WalletTopupRequest::with('vendor')->find($actionId); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($req): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="fi-card w-full max-w-md overflow-hidden">

                
                <div class="flex items-center justify-between border-b px-6 py-5
                    <?php echo e($actionType === 'approve' ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50'); ?>">
                    <div>
                        <h3 class="text-base font-semibold <?php echo e($actionType === 'approve' ? 'text-emerald-800' : 'text-red-800'); ?>">
                            <?php echo e($actionType === 'approve' ? '✓ Approve Request' : '✗ Reject Request'); ?>

                        </h3>
                        <p class="mt-0.5 text-xs <?php echo e($actionType === 'approve' ? 'text-emerald-600' : 'text-red-600'); ?>">
                            <?php echo e($req->vendor->business_name); ?> — <?php echo e($req->reference); ?>

                        </p>
                    </div>
                </div>

                
                <div class="p-6 space-y-4">
                    
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            ['Amount',       '₹'.number_format((float)$req->amount,2)],
                            ['Payment Mode', ucwords(str_replace('_',' ',$req->payment_mode))],
                            ['UTR / Ref',    $req->transaction_ref ?? '—'],
                            ['Bank',         $req->bank_name ?? '—'],
                            ['Remarks',      $req->remarks ?? '—'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l,$v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500"><?php echo e($l); ?></span>
                                <span class="text-xs font-semibold text-slate-800 <?php echo e($l==='Amount' ? 'text-base text-violet-700' : ''); ?>"><?php echo e($v); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actionType === 'approve'): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <p class="text-xs font-medium text-emerald-700">
                            ✓ Approving will immediately credit <strong>₹<?php echo e(number_format((float)$req->amount,2)); ?></strong> to <?php echo e($req->vendor->business_name); ?>'s wallet and create a ledger entry.
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                        <p class="text-xs font-medium text-red-700">
                            ✗ Rejecting this request will not credit any amount. The vendor will see your note.
                        </p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Admin Note <?php echo e($actionType === 'reject' ? '(Required)' : '(Optional)'); ?>

                        </label>
                        <textarea wire:model="adminNote" rows="3" class="fi-input text-sm"
                                  placeholder="<?php echo e($actionType === 'approve' ? 'e.g. Verified via bank statement' : 'Reason for rejection...'); ?>"></textarea>
                    </div>
                </div>

                
                <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button wire:click="$set('showApproveModal',false)"
                            class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button wire:click="confirm"
                            class="flex-1 rounded-xl py-2.5 text-sm font-semibold text-white
                                   <?php echo e($actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-500 hover:bg-red-600'); ?>">
                        <?php echo e($actionType === 'approve' ? 'Confirm & Credit Wallet' : 'Confirm Rejection'); ?>

                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/wallet-requests.blade.php ENDPATH**/ ?>