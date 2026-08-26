<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignedBankController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendor = $request->attributes->get('apiVendor');
        $base = rtrim($request->getSchemeAndHttpHost(), '/');

        $banks = $vendor->assignedBanks()
            ->with(['apiEndpoints' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(function (Bank $bank) use ($base) {
                return [
                    'code' => $bank->code,
                    'name' => $bank->name,
                    'environment' => $bank->environment,
                    'services' => $bank->services ?: ['imps', 'neft', 'rtgs'],
                    'endpoints' => $bank->apiEndpoints->map(fn ($ep) => [
                        'name' => $ep->name,
                        'method' => $ep->method,
                        'url' => $base.'/api/v1/bank/'.$bank->code.'/'.$ep->slug,
                        'description' => $ep->description,
                        'request_params' => $ep->request_params ?: [],
                        'response_params' => $ep->response_params ?: [],
                    ])->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $banks,
        ]);
    }
}
