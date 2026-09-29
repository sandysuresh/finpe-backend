<?php

namespace App\Http\Controllers\Api\V1\Aeps;

use App\Exceptions\AepsException;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Services\Aeps\AepsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AepsMerchantController extends Controller
{
    public function store(Request $request, AepsService $aeps): JsonResponse
    {
        $data = $request->validate([
            'client_reference' => 'required|string|max:80',
            'pipe' => 'required|string|max:10',
            'first_name' => 'required|string|max:80',
            'middle_name' => 'nullable|string|max:80',
            'last_name' => 'required|string|max:80',
            'dob' => 'required|date_format:d-m-Y',
            'email' => 'required|email|max:120',
            'phone' => 'required|string|max:15',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'state' => 'required|string|max:10',
            'district' => 'required|string|max:10',
            'gender' => 'required|in:M,F,O',
            'shop_name' => 'required|string|max:120',
            'mother_name' => 'required|string|max:120',
            'father_name' => 'required|string|max:120',
            'marital_status' => 'required|in:SINGLE,MARRIED',
            'mcc' => 'required|string|max:10',
            'device_ip' => 'required|ip',
            'pin_code' => 'required|string|max:10',
            'pan' => 'required|string|max:10',
            'aadhaar' => 'required|string|max:16',
            'shop_pan' => 'required|string|max:10',
            'bank_account' => 'required|string|max:30',
            'bank_ifsc' => 'required|string|max:20',
            'bank_name' => 'required|string|max:20',
            'account_type' => 'nullable|string|max:40',
            'shop_address' => 'required|string|max:255',
            'shop_district' => 'required|string|max:10',
            'shop_state' => 'required|string|max:10',
            'shop_pin' => 'required|string|max:10',
            'shop_lat' => 'required|string|max:20',
            'shop_long' => 'required|string|max:20',
            'lat' => 'required|string|max:20',
            'long' => 'required|string|max:20',
            'ip_address' => 'required|ip',
        ]);

        return $this->merchantResponse(fn () => $aeps->registerMerchant($request->attributes->get('apiVendor'), $data), 201);
    }

    public function sendOtp(Request $request, string $merchant, AepsService $aeps): JsonResponse
    {
        $data = $request->validate(['client_reference' => 'required|string|max:80']);

        return $this->merchantResponse(fn () => $aeps->sendOtp($request->attributes->get('apiVendor'), $merchant, $data['client_reference']));
    }

    public function resendOtp(Request $request, string $merchant, AepsService $aeps): JsonResponse
    {
        $data = $request->validate(['client_reference' => 'required|string|max:80']);

        return $this->merchantResponse(fn () => $aeps->resendOtp($request->attributes->get('apiVendor'), $merchant, $data['client_reference']));
    }

    public function verifyOtp(Request $request, string $merchant, AepsService $aeps): JsonResponse
    {
        $data = $request->validate([
            'client_reference' => 'required|string|max:80',
            'otp' => 'required|string|max:10',
        ]);

        return $this->merchantResponse(fn () => $aeps->verifyOtp(
            $request->attributes->get('apiVendor'),
            $merchant,
            $data['client_reference'],
            $data['otp'],
        ));
    }

    public function ekyc(Request $request, string $merchant, AepsService $aeps): JsonResponse
    {
        $data = $request->validate([
            'client_reference' => 'required|string|max:80',
            'pid_data' => 'required|string|max:20000',
        ]);

        return $this->merchantResponse(fn () => $aeps->ekyc(
            $request->attributes->get('apiVendor'),
            $merchant,
            $data['client_reference'],
            $data['pid_data'],
        ));
    }

    public function twoFactor(Request $request, string $merchant, AepsService $aeps): JsonResponse
    {
        $data = $request->validate([
            'client_reference' => 'required|string|max:80',
            'aadhaar' => 'required|string|max:16',
            'device_type' => 'required|string|max:40',
            'pid_data' => 'required|string|max:20000',
            'lat' => 'required|string|max:20',
            'long' => 'required|string|max:20',
        ]);

        return $this->merchantResponse(fn () => $aeps->twoFactor($request->attributes->get('apiVendor'), $merchant, $data));
    }

    private function merchantResponse(callable $call, int $status = 200): JsonResponse
    {
        try {
            $merchant = $call();
        } catch (AepsException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statusCode);
        }

        return response()->json([
            'success' => true,
            'data' => $this->payload($merchant),
        ], $status);
    }

    private function payload(Merchant $merchant): array
    {
        return [
            'merchant' => $merchant->code,
            'client_reference' => $merchant->client_reference,
            'onboarding_status' => $merchant->onboarding_status,
            'provider_status_code' => $merchant->provider_status_code,
            'provider_status_description' => $merchant->provider_status_description,
            'provider_ref' => $merchant->provider_ref,
            'aadhaar_masked' => $merchant->aadhaar_masked,
            'two_fa_at' => $merchant->two_fa_at?->toIso8601String(),
        ];
    }
}
