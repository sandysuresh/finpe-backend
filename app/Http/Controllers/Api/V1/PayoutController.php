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
        return $this->transfer($request, $payouts);
    }

    private function transfer(Request $request, PayoutService $payouts): JsonResponse
    {
        $data = $request->validate([
            'merchantRefId' => 'required|string|max:40',
            'amount' => 'required|numeric',
            'beneficiaryBank' => 'required|string|max:10',
            'paymentPurpose' => 'required|string|max:20',
            'paymentMode' => 'required|string|max:10',
            'beneficiaryAccountNumber' => 'required|string|max:32',
            'beneficiaryIFSC' => 'required|string|max:20',
            'beneficiaryMobileNumber' => 'required|string|max:15',
            'beneficiaryName' => 'required|string|max:120',
            'beneficiaryLocation' => 'required|string|max:20',
            'lat' => 'required|string|max:30',
            'long' => 'required|string|max:30',
            'udf1' => 'nullable|string|max:100',
            'udf2' => 'nullable|string|max:100',
            'udf3' => 'nullable|string|max:100',
        ]);

        $vendor = $request->attributes->get('apiVendor');

        try {
            $txn = $payouts->transfer($vendor, $data);
        } catch (PayoutException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->statusCode);
        }

        $message = match ($txn->status) {
            'success' => 'Payout successful.',
            'failed' => $txn->failure_reason ?: 'Payout failed.',
            default => 'Payout initiated.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'reference' => $txn->reference,
                'merchant_ref' => $txn->merchant_ref,
                'amount' => (float) $txn->amount,
                'charge' => (float) $txn->payout_charge,
                'total_debited' => round((float) $txn->amount + (float) $txn->payout_charge, 2),
                'payment_mode' => strtoupper((string) $txn->service),
                'beneficiary_bank' => $txn->beneficiary_bank_code,
                'account_number' => $this->maskAccount($txn->account_number),
                'ifsc' => $txn->ifsc_code,
                'provider_reference' => $txn->bank_reference,
                'status' => $txn->status,
                'created_at' => $txn->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    private function maskAccount(?string $account): string
    {
        $account = (string) $account;
        if (strlen($account) <= 4) {
            return str_repeat('X', strlen($account));
        }

        return str_repeat('X', strlen($account) - 4).substr($account, -4);
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
            'charge' => (float) $txn->payout_charge,
            'total_debited' => round((float) $txn->amount + (float) $txn->payout_charge, 2),
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
