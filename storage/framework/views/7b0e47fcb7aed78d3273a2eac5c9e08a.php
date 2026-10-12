<div class="fi-card mb-5 px-5 py-4">
    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showVendor): ?>
            <div style="width:240px; max-width:100%;">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Vendor</label>
                <select wire:model="vendor" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                    <option value="">All vendors</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>"><?php echo e($item->business_name); ?> (<?php echo e($item->vendor_code); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div>
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">From</label>
            <input type="date" wire:model="dateFrom" class="fi-input text-sm" style="height:40px; width:160px; padding:0 10px;">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">To</label>
            <input type="date" wire:model="dateTo" class="fi-input text-sm" style="height:40px; width:160px; padding:0 10px;">
        </div>
        <div style="width:180px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Payout provider</label>
            <select wire:model="provider" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                <option value="">All providers</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($item['code']); ?>"><?php echo e($item['name']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div style="width:180px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Type</label>
            <select wire:model="entry" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                <option value="">All types</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div style="width:160px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Status</label>
            <select wire:model="status" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                <option value="">All statuses</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($item); ?>"><?php echo e(ucfirst($item)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div style="width:240px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Search</label>
            <input type="text" wire:model="search" class="fi-input text-sm" style="height:40px;" placeholder="Vendor, reference, description">
        </div>
        <div style="display:flex; gap:8px; margin-left:auto;">
            <button type="button" wire:click="applyFilters" class="fi-btn fi-btn-primary">Apply</button>
            <button type="button" wire:click="resetFilters" class="fi-btn fi-btn-secondary">Reset</button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth('admin')->user()->hasPermission('reports', 'export')): ?>
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-secondary">Export Excel</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/partials/wallet-report-filters.blade.php ENDPATH**/ ?>