<?php

namespace App\Http\Controllers\Api\V1\Aeps;

use App\Exceptions\AepsException;
use App\Http\Controllers\Controller;
use App\Models\AepsTransaction;
use App\Services\Aeps\AepsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AepsTransactionController extends Controller
{
    public function otp(Request $request, AepsService $aeps): JsonResponse
    {
        $data = $this->validateIdentity($request, [
            'service' => 'required|in:CWTFA,APTFA',
            'amount' => 'required|numeric|min:5000.01|max:10000',
            'app_platform' => 'required|string|max:30',
            'app_version' => 'required|string|max:20',
            'customer_mobile' => 'nullable|string|max:15',
        ]);

        return $this->respond(fn () => $aeps->transactionOtp($request->attributes->get('apiVendor'), $data), 201);
    }

    public function store(Request $request, AepsService $aeps): JsonResponse
    {
        $data = $this->validateIdentity($request, [
            'service' => 'required|in:CW,BE,MS,AP,CD',
            'amount' => 'required|numeric|min:0|max:10000',
            'device_type' => 'required|string|max:40',
            'pid_data' => 'required|string|max:20000',
            'cw_auth_txn_id' => 'nullable|string|max:120',
            'udf1' => 'nullable|string|max:100',
            'udf2' => 'nullable|string|max:100',
            'udf3' => 'nullable|string|max:100',
        ]);

        return $this->respond(fn () => $aeps->transact($request->attributes->get('apiVendor'), $data), 201);
    }

    public function show(Request $request, string $reference, AepsService $aeps): JsonResponse
    {
        return $this->respond(fn () => $aeps->findTransaction($request->attributes->get('apiVendor'), $reference));
    }

    private function validateIdentity(Request $request, array $extra): array
    {
        return $request->validate(array_merge([
            'client_reference' => 'required|string|max:80',
            'merchant' => 'required|string|max:40',
            'aadhaar' => 'required|string|max:16',
            'mobile' => 'required|string|max:15',
            'bank_iin' => 'required|string|max:10',
            'lat' => 'required|string|max:20',
            'long' => 'required|string|max:20',
            'ip_address' => 'required|ip',
        ], $extra));
    }

    private function respond(callable $call, int $status = 200): JsonResponse
    {
        try {
            $txn = $call();
        } catch (AepsException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statusCode);
        }

        return response()->json([
            'success' => true,
            'data' => $this->payload($txn),
        ], $status);
    }

    private function payload(AepsTransaction $txn): array
    {
        return [
            'reference' => $txn->reference,
            'client_reference' => $txn->client_reference,
            'merchant' => $txn->merchant?->code,
            'service' => $txn->service,
            'amount' => (float) $txn->amount,
            'status' => $txn->status,
            'provider_status_code' => $txn->provider_status_code,
            'provider_txn_ref' => $txn->provider_txn_ref,
            'rrn' => $txn->rrn,
            'npci_code' => $txn->npci_code,
            'npci_message' => $txn->npci_message,
            'provider_status_description' => $txn->provider_status_description,
            'provider_available_balance' => $txn->provider_available_balance,
            'transaction_list' => $txn->provider_transaction_list,
            'aadhaar_masked' => $txn->aadhaar_masked,
            'bank_iin' => $txn->bank_iin,
            'wallet_effect' => $txn->wallet_effect,
            'charge_effect' => $txn->charge_effect,
            'failure_reason' => $txn->failure_reason,
            'created_at' => $txn->created_at?->toIso8601String(),
        ];
    }
}
