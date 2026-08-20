<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - FinPay Gateway</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-slate-50">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="hidden bg-blue-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">
                        <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m8 10 4-2 4 2-4 2-4-2Zm0 4 4 2 4-2"/></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">FinPay</div>
                        <div class="text-xs text-blue-100">Gateway</div>
                    </div>
                </div>
                <div class="mt-24 max-w-lg">
                    <p class="text-sm font-semibold uppercase tracking-[.2em] text-blue-100">FinTech Platform</p>
                    <h1 class="mt-4 text-5xl font-bold leading-tight">One secure platform for your payment ecosystem.</h1>
                    <p class="mt-6 text-base leading-7 text-blue-100">Manage vendors, wallets, transactions, settlements and APIs from one professional control center.</p>
                </div>
            </div>
            <div class="text-sm text-blue-100">© <?php echo e(date('Y')); ?> FinPay Gateway</div>
        </div>

        <div class="flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <div class="text-2xl font-bold text-slate-900">FinPay</div>
                    <div class="text-xs text-slate-500">Gateway</div>
                </div>

                <div class="fi-card p-8">
                    <div class="mb-7">
                        <h2 class="text-2xl font-bold text-slate-900">Admin Login</h2>
                        <p class="mt-1.5 text-sm text-slate-500">Sign in to access the FinPay control panel.</p>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-700">
                            <?php echo e($errors->first()); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <form method="POST" action="<?php echo e(route('admin.login.submit')); ?>" class="space-y-5">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus class="fi-input" placeholder="admin@finpay.test">
                        </div>
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="block text-sm font-semibold text-slate-700">Password</label>
                                <a href="#" class="text-xs font-semibold text-blue-600">Forgot password?</a>
                            </div>
                            <input type="password" name="password" required class="fi-input" placeholder="••••••••">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600">
                            Remember me
                        </label>
                        <button class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            Sign In
                        </button>
                    </form>

                    <div class="mt-6 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">
                        Demo: <strong>admin@finpay.test</strong> / <strong>password</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH /home/sandeep/Documents/finpay/resources/views/auth/admin-login.blade.php ENDPATH**/ ?>