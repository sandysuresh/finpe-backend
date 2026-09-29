<?php

use App\Http\Controllers\Api\V1\AssignedBankController;
use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\V1\BankServiceController;
use App\Http\Controllers\Api\V1\Aeps\AepsCatalogController;
use App\Http\Controllers\Api\V1\Aeps\AepsMerchantController;
use App\Http\Controllers\Api\V1\Aeps\AepsTransactionController;
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

    Route::get('/aeps/banks', [AepsCatalogController::class, 'banks']);
    Route::get('/aeps/bank-iin', [AepsCatalogController::class, 'bankIin']);
    Route::get('/aeps/states', [AepsCatalogController::class, 'states']);
    Route::get('/aeps/districts', [AepsCatalogController::class, 'districts']);
    Route::post('/aeps/merchants', [AepsMerchantController::class, 'store']);
    Route::post('/aeps/merchants/{merchant}/otp', [AepsMerchantController::class, 'sendOtp'])->where('merchant', '[A-Za-z0-9\-]+');
    Route::post('/aeps/merchants/{merchant}/otp/resend', [AepsMerchantController::class, 'resendOtp'])->where('merchant', '[A-Za-z0-9\-]+');
    Route::post('/aeps/merchants/{merchant}/otp/verify', [AepsMerchantController::class, 'verifyOtp'])->where('merchant', '[A-Za-z0-9\-]+');
    Route::post('/aeps/merchants/{merchant}/ekyc', [AepsMerchantController::class, 'ekyc'])->where('merchant', '[A-Za-z0-9\-]+');
    Route::post('/aeps/merchants/{merchant}/2fa', [AepsMerchantController::class, 'twoFactor'])->where('merchant', '[A-Za-z0-9\-]+');
    Route::post('/aeps/transaction-otp', [AepsTransactionController::class, 'otp']);
    Route::post('/aeps/transactions', [AepsTransactionController::class, 'store']);
    Route::get('/aeps/transactions/{reference}', [AepsTransactionController::class, 'show'])->where('reference', '[A-Za-z0-9\-]+');
});
