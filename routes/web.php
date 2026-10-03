<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\VendorAuthController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Vendors\Index as VendorIndex;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware('auth:admin')->name('admin.logout');

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->middleware('admin.module:dashboard')->name('dashboard');
    Route::get('/commission/summary', \App\Livewire\Admin\CommissionSummary::class)->middleware('admin.module:dashboard')->name('commission.summary');
    Route::middleware('admin.module:dashboard')->group(function () {
        Route::get('/app-users', \App\Livewire\Admin\AppUsers::class)->name('app-users.list');
        Route::get('/app-users/details', \App\Livewire\Admin\AppUsers::class)->name('app-users.details');
        Route::get('/app-users/kyc', \App\Livewire\Admin\AppUsers::class)->name('app-users.kyc');
        Route::get('/app-users/history', \App\Livewire\Admin\AppUsers::class)->name('app-users.history');
        Route::get('/app-users/activity', \App\Livewire\Admin\AppUsers::class)->name('app-users.activity');
        Route::get('/app-users/block', \App\Livewire\Admin\AppUsers::class)->name('app-users.block');
    });
    Route::get('/merchants', \App\Livewire\Admin\Merchants::class)->middleware('admin.module:vendors')->name('merchants');
    Route::middleware('admin.module:commission')->group(function () {
        Route::get('/commission', \App\Livewire\Admin\CommissionRules::class)->name('commission.rules');
        Route::get('/commission/history', \App\Livewire\Admin\CommissionHistory::class)->name('commission.history');
        Route::get('/commission/partners', \App\Livewire\Admin\AgentCommission::class)->name('commission.partners');
        Route::get('/commission/merchants', \App\Livewire\Admin\MerchantCommission::class)->name('commission.merchants');
        Route::get('/commission/settlement', \App\Livewire\Admin\CommissionSettlements::class)->name('commission.settlement');
    });
    Route::get('/settlements', \App\Livewire\Admin\Placeholder::class)->middleware('admin.module:wallet-requests')->name('settlements');
    Route::get('/vendors', VendorIndex::class)->middleware('admin.module:vendors')->name('vendors');
    Route::middleware('admin.module:vendors')->group(function () {
        Route::get('/partners/kyc', \App\Livewire\Admin\PartnerSection::class)->name('partners.kyc');
        Route::get('/partners/wallet', \App\Livewire\Admin\PartnerSection::class)->name('partners.wallet');
        Route::get('/partners/transactions', \App\Livewire\Admin\PartnerSection::class)->name('partners.transactions');
        Route::get('/partners/commission', \App\Livewire\Admin\PartnerSection::class)->name('partners.commission');
        Route::get('/partners/settlement', \App\Livewire\Admin\PartnerSection::class)->name('partners.settlement');
    });
    Route::get('/vendors/create/{vendor?}', \App\Livewire\Admin\Vendors\Create::class)
        ->middleware('admin.module:vendors')
        ->where('vendor', '[A-Za-z0-9\-_]+')
        ->name('vendors.create');
    Route::get('/vendors/{vendor}', \App\Livewire\Admin\Vendors\Show::class)
        ->middleware('admin.module:vendors')
        ->where('vendor', '[A-Za-z0-9\-_]+')
        ->name('vendors.show');
    Route::get('/wallet-requests', \App\Livewire\Admin\WalletRequests::class)->middleware('admin.module:wallet-requests')->name('wallet-requests');
    Route::middleware('admin.module:transactions')->group(function () {
        Route::get('/transactions', \App\Livewire\Admin\Transactions::class)->name('transactions');
        Route::get('/transactions/history', \App\Livewire\Admin\Transactions::class)->name('txn-history');
        Route::get('/transactions/successful', \App\Livewire\Admin\Transactions::class)->name('txn-success');
        Route::get('/transactions/pending', \App\Livewire\Admin\Transactions::class)->name('txn-pending');
        Route::get('/transactions/failed', \App\Livewire\Admin\Transactions::class)->name('txn-failed');
        Route::get('/transactions/reversed', \App\Livewire\Admin\Transactions::class)->name('txn-reversed');
        Route::get('/transactions/search', \App\Livewire\Admin\Transactions::class)->name('txn-search');
        Route::get('/transactions/details', \App\Livewire\Admin\Transactions::class)->name('txn-details');
        Route::get('/aeps-transactions', \App\Livewire\Admin\AepsTransactions::class)->name('aeps-transactions');
        Route::get('/payout-transactions', \App\Livewire\Admin\PayoutTransactions::class)->name('payout-transactions');
        Route::get('/payout-transactions/{reference}/receipt', \App\Http\Controllers\Admin\PayoutReceiptController::class)
            ->where('reference', '[A-Za-z0-9\-]+')
            ->name('payout-transactions.receipt');
        Route::get('/aeps/api-documentation', \App\Http\Controllers\Admin\AepsApiDocController::class)
            ->name('aeps.api-documentation');
        Route::get('/aeps-transactions/{reference}/receipt', \App\Http\Controllers\Admin\AepsReceiptController::class)
            ->where('reference', '[A-Za-z0-9\-]+')
            ->name('aeps-transactions.receipt');
        Route::get('/aeps-transactions/{reference}', \App\Livewire\Admin\AepsTransactions::class)
            ->where('reference', '[A-Za-z0-9\-]+')
            ->name('aeps-transactions.show');
    });
    Route::get('/payout-api', \App\Livewire\Admin\PayoutApiTest::class)->middleware('admin.module:payout-api')->name('payout-api');
    Route::get('/reports', \App\Livewire\Admin\Reports::class)->middleware('admin.module:reports')->name('reports');
    Route::get('/daily-balance', \App\Livewire\Admin\DailyBalance::class)->middleware('admin.module:reports')->name('daily-balance');
    Route::get('/reports/{report}', \App\Livewire\Admin\ReportShow::class)
        ->middleware('admin.module:reports')
        ->where('report', '[A-Za-z0-9\-_]+')
        ->name('reports.show');
    Route::get('/users', \App\Livewire\Admin\Users::class)->middleware('admin.module:users')->name('users');
    Route::get('/roles', \App\Livewire\Admin\Roles::class)->middleware('admin.module:users')->name('roles');
    Route::get('/permissions', \App\Livewire\Admin\RolePermissions::class)->middleware('admin.module:users')->name('permissions');
    Route::get('/approval-workflow', \App\Livewire\Admin\Placeholder::class)->middleware('admin.module:users')->name('approval-workflow');
    Route::get('/system-settings', \App\Livewire\Admin\Placeholder::class)->middleware('admin.module:users')->name('system-settings');
    Route::get('/audit-logs', \App\Livewire\Admin\AuditLogs::class)->middleware('admin.module:users')->name('audit-logs');
    Route::get('/banks', \App\Livewire\Admin\Banks::class)->middleware('admin.module:banks')->name('banks');
    Route::get('/api-logs', \App\Livewire\Admin\ApiLogs::class)->middleware('admin.module:banks')->name('api-logs');
});

Route::get('/vendor/login', [VendorAuthController::class, 'showLogin'])->name('vendor.login');
Route::post('/vendor/login', [VendorAuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('vendor.login.submit');
Route::post('/vendor/logout', [VendorAuthController::class, 'logout'])
    ->middleware('auth:vendor')->name('vendor.logout');

Route::middleware('auth:vendor')->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Vendor\Dashboard::class)->name('dashboard');
    Route::get('/wallet', \App\Livewire\Vendor\Wallet::class)->name('wallet');
    Route::get('/send-money', \App\Livewire\Vendor\SendMoney::class)->name('send-money');
    Route::get('/beneficiaries', \App\Livewire\Vendor\Beneficiaries::class)->name('beneficiaries');
    Route::get('/transactions', \App\Livewire\Vendor\TransactionReport::class)->name('transactions');
    Route::get('/reports', \App\Livewire\Vendor\Reports::class)->name('reports');
    Route::get('/settlements', \App\Livewire\Vendor\SettlementReport::class)->name('settlements');
    Route::get('/developer', \App\Livewire\Vendor\Developer::class)->name('developer');
    Route::get('/aeps/api-documentation', \App\Http\Controllers\Admin\AepsApiDocController::class)->name('aeps.api-documentation');
    Route::get('/profile', \App\Livewire\Vendor\Profile::class)->name('profile');
});
