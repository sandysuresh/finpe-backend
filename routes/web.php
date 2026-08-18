<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Vendors\Index as VendorIndex;


Route::get('/', fn () => redirect()->route('admin.login'));

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->name('admin.login.submit');

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware('auth:admin')
    ->name('admin.logout');

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)
        ->name('dashboard');

    Route::get('/vendors', VendorIndex::class)
        ->name('vendors');

    Route::get('/vendors/create', \App\Livewire\Admin\Vendors\Create::class)
        ->name('vendors.create');

    Route::get('/vendors/{vendor}', \App\Livewire\Admin\Vendors\Show::class)
        ->name('vendors.show');
});