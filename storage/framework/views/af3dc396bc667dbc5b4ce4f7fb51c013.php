<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Dashboard'); ?> - FinPe Gateway</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="min-h-screen overflow-x-hidden font-sans antialiased" style="--fi-accent:#1d4ed8">
    <header class="sticky top-0 z-50 border-b border-slate-800 bg-slate-900">
        <div class="mx-auto flex h-[70px] min-w-0 max-w-[1600px] items-center px-5">
            <a href="<?php echo e(\App\Support\AdminModules::firstUrl(auth('admin')->user())); ?>" class="mr-4 flex shrink-0 items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3Z"/>
                        <path d="m8 10 4-2 4 2-4 2-4-2Zm0 4 4 2 4-2"/>
                    </svg>
                </div>
                <div class="leading-tight">
                    <div class="text-[20px] font-bold tracking-tight text-white">FinPe</div>
                    <div class="text-[11px] font-medium text-slate-300">Admin Gateway</div>
                </div>
            </a>

            <nav class="hidden min-w-0 flex-1 items-center gap-0.5 xl:flex" x-data="{ open: null }" @keydown.escape.window="open = null">
                <?php
                    $menus = \App\Support\AdminModules::navItems(auth('admin')->user());
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['children'])): ?>
                        <div class="relative" @click.outside="if (open === <?php echo e($loop->index); ?>) open = null">
                            <button type="button" @click="open = open === <?php echo e($loop->index); ?> ? null : <?php echo e($loop->index); ?>" class="flex items-center gap-1 whitespace-nowrap rounded-lg px-2 py-1.5 text-[12px] font-semibold <?php echo e($item['active'] ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?>" :aria-expanded="open === <?php echo e($loop->index); ?>">
                                <?php echo e($item['label']); ?>

                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open === <?php echo e($loop->index); ?>" x-cloak class="absolute <?php echo e($loop->remaining < 3 ? 'right-0' : 'left-0'); ?> top-full z-50 pt-2">
                                <div class="w-64 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e($child['url']); ?>" class="block rounded-lg px-3 py-2 text-sm font-semibold <?php echo e($child['active'] ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50'); ?>"><?php echo e($child['label']); ?></a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e($item['url']); ?>" class="flex items-center gap-1 whitespace-nowrap rounded-lg px-2 py-1.5 text-[12px] font-semibold <?php echo e($item['active'] ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?>">
                            <?php echo e($item['label']); ?>

                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <button class="hidden rounded-full p-2 text-slate-300 hover:bg-slate-800 hover:text-white sm:block" title="Theme">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                    </svg>
                </button>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('admin.notification-bell');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3279276699-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

                <div class="hidden h-8 w-px bg-slate-700 sm:block"></div>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" @click="open = !open" class="flex items-center gap-2.5 rounded-lg px-1.5 py-1 hover:bg-slate-800" :aria-expanded="open">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">
                            <?php echo e(strtoupper(substr(auth('admin')->user()->name, 0, 1))); ?>

                        </span>
                        <span class="hidden text-left lg:block">
                            <span class="block text-[13px] font-semibold text-white"><?php echo e(auth('admin')->user()->name); ?></span>
                            <span class="block text-[11px] text-slate-300"><?php echo e(auth('admin')->user()->roleLabel()); ?></span>
                        </span>
                        <svg class="hidden h-4 w-4 text-slate-400 lg:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 top-full z-50 w-48 pt-2">
                        <div class="rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                            <div class="px-3 py-2 text-xs text-slate-500">Account</div>
                            <a href="#" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
                            <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-800 xl:hidden">
            <nav class="fi-scroll flex gap-1 overflow-x-auto px-4 py-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['children'])): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($child['url']); ?>" class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-semibold <?php echo e($child['active'] ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?>"><?php echo e($child['label']); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo e($item['url']); ?>" class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-semibold <?php echo e($item['active'] ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'); ?>"><?php echo e($item['label']); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="mx-auto min-w-0 max-w-[1600px] overflow-x-hidden px-5 py-5">
        <?php echo e($slot); ?>

    </main>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/layouts/admin.blade.php ENDPATH**/ ?>