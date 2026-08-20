<div class="min-h-full bg-slate-50 p-6">

    
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Dashboard
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Welcome back to FinPay Admin Panel.
            </p>
        </div>

        <div class="hidden items-center gap-3 sm:flex">
            <button
                type="button"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Export
            </button>

            <a
                href="<?php echo e(route('admin.vendors')); ?>"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-violet-50 hover:text-violet-600"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-5a4 4 0 11-8 0 4 4 0 018 0z"
                    />
                </svg>

                <span>Vendors</span>
            </a>
        </div>
    </div>


    
    
    

    <div
        class="grid w-full grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
        style="display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px;"
    >

        
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Total Balance
                    </p>

                    <h2 class="mt-3 text-2xl font-bold text-slate-900">
                        ₹ 12,45,800
                    </h2>

                    <div class="mt-2 flex items-center gap-1 text-xs font-medium text-emerald-600">
                        <span>↑ 8.4%</span>
                        <span class="text-slate-400">this month</span>
                    </div>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    ₹
                </div>

            </div>

        </div>


        
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Total Vendors
                    </p>

                    <h2 class="mt-3 text-2xl font-bold text-slate-900">
                        128
                    </h2>

                    <div class="mt-2 flex items-center gap-1 text-xs font-medium text-emerald-600">
                        <span>↑ 12</span>
                        <span class="text-slate-400">this month</span>
                    </div>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-5a4 4 0 11-8 0 4 4 0 018 0zm-2 7a4 4 0 00-4-4 4 4 0 00-4 4"/>
                    </svg>
                </div>

            </div>

        </div>


        
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Today's Transactions
                    </p>

                    <h2 class="mt-3 text-2xl font-bold text-slate-900">
                        2,846
                    </h2>

                    <div class="mt-2 text-xs text-slate-400">
                        Total processed today
                    </div>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5h6M9 9h6M9 13h4m-7 8h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>

            </div>

        </div>


        
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Today's Volume
                    </p>

                    <h2 class="mt-3 text-2xl font-bold text-slate-900">
                        ₹ 38.42 L
                    </h2>

                    <div class="mt-2 flex items-center gap-1 text-xs font-medium text-emerald-600">
                        <span>↑ 5.2%</span>
                        <span class="text-slate-400">from yesterday</span>
                    </div>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-15v2m0 13v2"/>
                    </svg>
                </div>

            </div>

        </div>

    </div>


    
    
    

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">


        
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

                <div>
                    <h2 class="text-base font-semibold text-slate-900">
                        Transaction Overview
                    </h2>

                    <p class="mt-1 text-xs text-slate-400">
                        Transaction volume for the current period
                    </p>
                </div>

                <select class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 outline-none">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                    <option>Last 3 Months</option>
                </select>

            </div>


            
            <div class="px-6 py-6">

                <div class="flex h-64 items-end gap-4">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [42,58,48,72,61,85,70,92,76,88,68,96]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $height): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <div class="flex flex-1 flex-col items-center justify-end gap-2">

                            <div
                                class="w-full rounded-t-lg bg-violet-500 opacity-90"
                                style="height: <?php echo e($height); ?>%;"
                            ></div>

                        </div>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </div>

                <div class="mt-3 flex justify-between text-xs text-slate-400">
                    <span>01 Aug</span>
                    <span>03 Aug</span>
                    <span>05 Aug</span>
                    <span>07 Aug</span>
                    <span>09 Aug</span>
                    <span>11 Aug</span>
                </div>

            </div>

        </div>


        
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-base font-semibold text-slate-900">
                    Transaction Status
                </h2>

                <p class="mt-1 text-xs text-slate-400">
                    Today's transaction summary
                </p>
            </div>


            <div class="space-y-5 p-6">

                
                <div>
                    <div class="mb-2 flex justify-between">
                        <span class="text-sm text-slate-600">
                            Successful
                        </span>

                        <span class="text-sm font-semibold text-emerald-600">
                            82%
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[82%] rounded-full bg-emerald-500"></div>
                    </div>
                </div>


                
                <div>
                    <div class="mb-2 flex justify-between">
                        <span class="text-sm text-slate-600">
                            Pending
                        </span>

                        <span class="text-sm font-semibold text-amber-600">
                            11%
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[11%] rounded-full bg-amber-500"></div>
                    </div>
                </div>


                
                <div>
                    <div class="mb-2 flex justify-between">
                        <span class="text-sm text-slate-600">
                            Failed
                        </span>

                        <span class="text-sm font-semibold text-red-600">
                            7%
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[7%] rounded-full bg-red-500"></div>
                    </div>
                </div>


                <div class="border-t border-slate-100 pt-5">

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">
                            Total Transactions
                        </span>

                        <span class="font-semibold text-slate-900">
                            2,846
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-sm text-slate-500">
                            Total Volume
                        </span>

                        <span class="font-semibold text-slate-900">
                            ₹38.42 L
                        </span>
                    </div>

                </div>

            </div>

        </div>

    </div>


    
    
    

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    Recent Transactions
                </h2>

                <p class="mt-1 text-xs text-slate-400">
                    Latest payment activity
                </p>
            </div>

            <button class="text-sm font-medium text-violet-600 hover:text-violet-700">
                View All →
            </button>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">

                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Transaction
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Vendor
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Amount
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Type
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Date
                        </th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['id' => 'TXN-10001', 'vendor' => 'ABC Remittance', 'amount' => '₹25,000', 'type' => 'Send Money', 'status' => 'Success', 'date' => '12 Aug, 09:42 AM'],
                        ['id' => 'TXN-10002', 'vendor' => 'Global Money', 'amount' => '₹18,500', 'type' => 'Add Money', 'status' => 'Success', 'date' => '12 Aug, 09:31 AM'],
                        ['id' => 'TXN-10003', 'vendor' => 'FastSend Nepal', 'amount' => '₹42,000', 'type' => 'Send Money', 'status' => 'Pending', 'date' => '12 Aug, 09:18 AM'],
                        ['id' => 'TXN-10004', 'vendor' => 'PayLink', 'amount' => '₹12,800', 'type' => 'Settlement', 'status' => 'Failed', 'date' => '12 Aug, 08:56 AM'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <tr class="hover:bg-slate-50">

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-semibold text-slate-900">
                                    <?php echo e($transaction['id']); ?>

                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                <?php echo e($transaction['vendor']); ?>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-900">
                                <?php echo e($transaction['amount']); ?>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                <?php echo e($transaction['type']); ?>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transaction['status'] === 'Success'): ?>

                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        Success
                                    </span>

                                <?php elseif($transaction['status'] === 'Pending'): ?>

                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                        Pending
                                    </span>

                                <?php else: ?>

                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                        Failed
                                    </span>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                <?php echo e($transaction['date']); ?>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div><?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>