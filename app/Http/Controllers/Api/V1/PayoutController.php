<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\PayoutException;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function store(Request $request, PayoutService $payouts): JsonResponse
    {
        $data = $request->validate([
            'beneficiary_name' => 'required|string|max:120',
            'account_number' => 'required|string|min:8|max:32',
            'ifsc_code' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:1|max:10000000',
            'bank_code' => 'nullable|string|max:30',
            'service' => 'nullable|in:imps,neft,rtgs,IMPS,NEFT,RTGS',
            'remarks' => 'nullable|string|max:200',
        ]);

        $data['service'] = strtolower($data['service'] ?? 'imps');
        $vendor = $request->attributes->get('apiVendor');

        try {
            $txn = $payouts->send($vendor, $data, 'api')->load('bank');
        } catch (PayoutException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout initiated.',
            'data' => $this->payload($txn),
        ], 201);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        $vendor = $request->attributes->get('apiVendor');
        $txn = Transaction::query()
            ->with('bank')
            ->where('vendor_id', $vendor->id)
            ->where('reference', $reference)
            ->first();

        if (! $txn) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->payload($txn),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $vendor = $request->attributes->get('apiVendor');
        $rows = Transaction::query()
            ->with('bank')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Transaction $txn) => $this->payload($txn));

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    private function payload(Transaction $txn): array
    {
        return [
            'reference' => $txn->reference,
            'bank_code' => $txn->bank?->code,
            'bank_reference' => $txn->bank_reference,
            'amount' => (float) $txn->amount,
            'service' => $txn->service,
            'status' => $txn->status,
            'beneficiary_name' => $txn->beneficiary_name,
            'account_number' => $txn->account_number,
            'ifsc_code' => $txn->ifsc_code,
            'failure_reason' => $txn->failure_reason,
            'created_at' => $txn->created_at?->toIso8601String(),
        ];
    }
}
