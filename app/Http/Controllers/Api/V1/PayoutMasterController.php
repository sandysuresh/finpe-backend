<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\PayoutException;
use App\Http\Controllers\Controller;
use App\Services\Payout\PayoutMasterProvider;
use Illuminate\Http\JsonResponse;

class PayoutMasterController extends Controller
{
    public function banks(PayoutMasterProvider $master): JsonResponse
    {
        return $this->respond(fn () => $master->banks());
    }

    public function purposes(PayoutMasterProvider $master): JsonResponse
    {
        return $this->respond(fn () => $master->purposes());
    }

    public function states(PayoutMasterProvider $master): JsonResponse
    {
        return $this->respond(fn () => $master->states());
    }

    private function respond(callable $call): JsonResponse
    {
        try {
            $result = $call();
        } catch (PayoutException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->statusCode);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
}
