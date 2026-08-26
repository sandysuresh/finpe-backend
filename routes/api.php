<?php

use App\Http\Controllers\Api\V1\AssignedBankController;
use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\V1\BankServiceController;
use App\Http\Controllers\Api\V1\PayoutController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['success' => true]))
    ->middleware('throttle:30,1');

Route::prefix('v1')->middleware(['throttle:vendor-api', 'vendor.api', 'vendor.api.log'])->group(function () {
    Route::get('/balance', [BalanceController::class, 'show']);
    Route::get('/banks', [AssignedBankController::class, 'index']);
    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::post('/payouts', [PayoutController::class, 'store']);
    Route::get('/payouts/{reference}', [PayoutController::class, 'show']);
    Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/bank/{bankCode}/{slug}', [BankServiceController::class, 'handle'])
        ->where(['bankCode' => '[A-Za-z0-9_-]+', 'slug' => '[A-Za-z0-9_-]+']);
});
