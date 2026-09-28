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
    Route::get('/vendors', VendorIndex::class)->middleware('admin.module:vendors')->name('vendors');
    Route::get('/vendors/create/{vendor?}', \App\Livewire\Admin\Vendors\Create::class)
        ->middleware('admin.module:vendors')
        ->where('vendor', '[A-Za-z0-9\-_]+')
        ->name('vendors.create');
    Route::get('/vendors/{vendor}', \App\Livewire\Admin\Vendors\Show::class)
        ->middleware('admin.module:vendors')
        ->where('vendor', '[A-Za-z0-9\-_]+')
        ->name('vendors.show');
    Route::get('/wallet-requests', \App\Livewire\Admin\WalletRequests::class)->middleware('admin.module:wallet-requests')->name('wallet-requests');
    Route::get('/transactions', \App\Livewire\Admin\Transactions::class)->middleware('admin.module:transactions')->name('transactions');
    Route::get('/reports', \App\Livewire\Admin\Reports::class)->middleware('admin.module:reports')->name('reports');
    Route::get('/daily-balance', \App\Livewire\Admin\DailyBalance::class)->middleware('admin.module:reports')->name('daily-balance');
    Route::get('/reports/{report}', \App\Livewire\Admin\ReportShow::class)
        ->middleware('admin.module:reports')
        ->where('report', '[A-Za-z0-9\-_]+')
        ->name('reports.show');
    Route::get('/users', \App\Livewire\Admin\Users::class)->middleware('admin.module:users')->name('users');
    Route::get('/banks', \App\Livewire\Admin\Banks::class)->middleware('admin.module:banks')->name('banks');
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
    Route::get('/profile', \App\Livewire\Vendor\Profile::class)->name('profile');
});
