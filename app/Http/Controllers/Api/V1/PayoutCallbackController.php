<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\PayoutException;
use App\Http\Controllers\Controller;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutCallbackController extends Controller
{
    public function __invoke(Request $request, PayoutService $payouts): JsonResponse
    {
        $payload = $request->validate([
            'beneficiaryMobileNumber' => 'required|string|max:15',
            'beneficiaryBank' => 'required|string|max:10',
            'beneficiaryAccountNumber' => 'required|string|max:32',
            'beneficiaryIFSC' => 'required|string|max:20',
            'beneficiaryLocation' => 'required|string|max:20',
            'beneficiaryName' => 'required|string|max:120',
            'merchantRefId' => 'required|string|max:40',
            'amount' => 'required|numeric|lt:100000',
            'charges' => 'required|numeric',
            'paymentMode' => 'required|in:IMPS,NEFT,imps,neft',
            'paymentPurpose' => 'required|string|max:20',
            'lat' => 'required',
            'long' => 'required',
            'udf1' => 'nullable|string|max:100',
            'udf2' => 'nullable|string|max:100',
            'udf3' => 'nullable|string|max:100',
            'txnStatus' => 'required|string|max:40',
            'txnStatusCode' => 'required|string|size:3',
            'txnId' => 'nullable|string|max:80',
            'rrn' => 'nullable|string|max:40',
            'responseMessage' => 'required|string|max:255',
        ]);

        try {
            $payouts->applyProviderCallback($payload);
        } catch (PayoutException $e) {
            return response()->json([
                'successStatus' => false,
                'message' => 'Invalid callback.',
            ], $e->statusCode);
        }

        return response()->json([
            'successStatus' => true,
            'message' => 'Success',
            'responseCode' => '000',
        ]);
    }
}
