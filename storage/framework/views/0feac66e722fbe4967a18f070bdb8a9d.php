<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Upload Logo</h1>
        <p class="mt-1 text-sm text-slate-500">Profile &amp; KYC. This logo appears in your vendor panel header.</p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($successMsg): ?>
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"><?php echo e($successMsg); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="fi-card max-w-xl p-6">
        <p class="text-sm font-semibold text-slate-900">Current logo</p>
        <div class="mt-4 flex h-24 items-center">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentLogo): ?>
                <img src="<?php echo e(asset('storage/'.$currentLogo)); ?>" alt="Uploaded logo" class="max-h-20 max-w-full object-contain">
            <?php else: ?>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3Z"/>
                            <path d="m8 10 4-2 4 2-4 2-4-2Zm0 4 4 2 4-2"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-600">Default FinPe logo</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <form wire:submit="save" class="mt-6 space-y-4">
            <label class="block text-sm font-medium text-slate-700">
                Company logo
                <input wire:model="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm text-slate-600">
            </label>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p class="text-xs text-slate-500">PNG, JPG, JPEG, or WebP. Maximum 2 MB. Between 32 and 4000 pixels on each side.</p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo): ?>
                <img src="<?php echo e($logo->temporaryUrl()); ?>" alt="Logo preview" class="max-h-20 max-w-full object-contain">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white">Save logo</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentLogo): ?>
                    <button type="button" wire:click="remove" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Remove logo</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/logo.blade.php ENDPATH**/ ?>