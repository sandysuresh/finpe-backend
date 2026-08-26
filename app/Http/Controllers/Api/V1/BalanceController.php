<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $vendor = $request->attributes->get('apiVendor');
        $wallet = $vendor->wallet;

        return response()->json([
            'success' => true,
            'data' => [
                'vendor_code' => $vendor->vendor_code,
                'available_balance' => (float) ($wallet->balance ?? 0),
                'hold_balance' => (float) ($wallet->hold_balance ?? 0),
                'currency' => 'INR',
            ],
        ]);
    }
}
