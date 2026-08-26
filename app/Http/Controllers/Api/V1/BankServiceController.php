<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankApiEndpoint;
use App\Services\Banking\BankEndpointClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankServiceController extends Controller
{
    public function handle(Request $request, string $bankCode, string $slug, BankEndpointClient $client): JsonResponse
    {
        $vendor = $request->attributes->get('apiVendor');

        $bank = $vendor->assignedBanks()
            ->where('banks.code', strtoupper($bankCode))
            ->first();

        if (! $bank) {
            return response()->json([
                'success' => false,
                'message' => 'This bank API is not assigned to your account.',
            ], 403);
        }

        $endpoint = BankApiEndpoint::query()
            ->where('bank_id', $bank->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown API endpoint.',
            ], 404);
        }

        if (strtoupper($endpoint->method) !== strtoupper($request->method())) {
            return response()->json([
                'success' => false,
                'message' => 'Use '.$endpoint->method.' for this endpoint.',
            ], 405);
        }

        $payload = strtoupper($endpoint->method) === 'GET'
            ? $request->query()
            : $request->all();

        $allowed = collect($endpoint->request_params ?? [])
            ->pluck('name')
            ->filter()
            ->all();
        if ($allowed !== []) {
            $payload = array_intersect_key($payload, array_flip($allowed));
        } else {
            $payload = [];
        }

        foreach ($endpoint->request_params ?? [] as $param) {
            if (! empty($param['required']) && (! array_key_exists($param['name'], $payload) || $payload[$param['name']] === '' || $payload[$param['name']] === null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameter: '.$param['name'],
                ], 422);
            }
        }

        $result = $client->dispatch($bank, $endpoint, $payload);

        return response()->json([
            'success' => (bool) ($result['success'] ?? false),
            'message' => $result['message'] ?? null,
            'data' => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 422);
    }
}
