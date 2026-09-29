<?php

namespace App\Http\Controllers\Api\V1\Aeps;

use App\Exceptions\AepsException;
use App\Http\Controllers\Controller;
use App\Services\Aeps\AepsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AepsCatalogController extends Controller
{
    public function banks(AepsService $aeps): JsonResponse
    {
        return $this->respond(fn () => $aeps->banks());
    }

    public function bankIin(Request $request, AepsService $aeps): JsonResponse
    {
        $data = $request->validate([
            'txn_code' => 'required|in:CW,BE,MS,AP,CD',
            'auth_type' => 'required|in:BA,FA',
        ]);

        return $this->respond(fn () => $aeps->bankIin($data['txn_code'], $data['auth_type']));
    }

    public function states(AepsService $aeps): JsonResponse
    {
        return $this->respond(fn () => $aeps->states());
    }

    public function districts(Request $request, AepsService $aeps): JsonResponse
    {
        $data = $request->validate([
            'state_code' => 'required|string|max:10',
        ]);

        return $this->respond(fn () => $aeps->districts($data['state_code']));
    }

    private function respond(callable $call): JsonResponse
    {
        try {
            $result = $call();
        } catch (AepsException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statusCode);
        }

        return response()->json([
            'success' => true,
            'data' => $result['list'] ?? $result['sanitized'] ?? [],
        ]);
    }
}
