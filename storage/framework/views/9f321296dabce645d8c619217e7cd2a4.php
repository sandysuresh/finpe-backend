<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($id) && !empty($reference) && ($type ?? 'payout') === 'payout'): ?>
    <span style="display:inline-flex; align-items:center; gap:6px; width:auto;" onclick="event.stopPropagation()">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($livewire)): ?>
            <button type="button" wire:click.stop="show(<?php echo e((int) $id); ?>)" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:28px; padding:0 8px; font-size:11px;">View</button>
        <?php else: ?>
            <a href="<?php echo e(route('admin.payout-transactions', ['view' => $id])); ?>" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:28px; padding:0 8px; font-size:11px; text-decoration:none;">View</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <a href="<?php echo e(route('admin.payout-transactions.receipt', $reference)); ?>" class="fi-btn fi-btn-sm fi-btn-secondary" style="width:auto; height:28px; padding:0 8px; font-size:11px; text-decoration:none;">PDF</a>
    </span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/partials/txn-actions.blade.php ENDPATH**/ ?>