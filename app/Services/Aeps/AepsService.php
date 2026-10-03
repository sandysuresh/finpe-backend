<?php

namespace App\Services\Aeps;

use App\Exceptions\AepsException;
use App\Models\AepsTransaction;
use App\Models\Merchant;
use App\Models\Vendor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

/**
 * Persists AePS provider results only.
 * Vendor wallet debit, credit, charge posting, settlement, and reversal are BUSINESS_RULE_PENDING.
 */
class AepsService
{
    public function __construct(private AepsProvider $provider) {}

    public function banks(): array
    {
        return $this->provider->banks();
    }

    public function bankIin(string $txnCode, string $authType): array
    {
        return $this->provider->bankIin($txnCode, $authType);
    }

    public function states(): array
    {
        return $this->provider->states();
    }

    public function districts(string $stateCode): array
    {
        return $this->provider->districts($stateCode);
    }

    public function registerMerchant(Vendor $vendor, array $input): Merchant
    {
        $hash = AepsRequestFingerprint::registration($vendor->id, $input);

        try {
            $merchant = Merchant::create([
                'vendor_id' => $vendor->id,
                'code' => 'MCH-'.strtoupper(Str::random(10)),
                'client_reference' => $input['client_reference'],
                'registration_hash' => $hash,
                'pipe' => (string) $input['pipe'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'phone' => $input['phone'],
                'phone_masked' => AepsPayloadSanitizer::maskPhone($input['phone']),
                'aadhaar_masked' => AepsPayloadSanitizer::maskDigits($input['aadhaar']),
                'onboarding_status' => AepsStatus::INITIATED,
            ]);
        } catch (QueryException $e) {
            $existing = $this->findMerchantByClientReference($vendor, $input['client_reference']);
            if ($existing && $this->isDuplicateKey($e)) {
                return $this->replayMerchant($existing, $hash);
            }
            throw $e;
        }

        try {
            $result = $this->provider->registerMerchant($this->registrationPayload($input));
        } catch (AepsException $e) {
            $merchant->update(['onboarding_status' => AepsStatus::PROVIDER_ERROR]);
            throw $e;
        }

        $this->applyProviderResult($merchant, $result);
        $merchant->registration_hash = $hash;
        $merchant->save();

        return $merchant->fresh();
    }

    public function sendOtp(Vendor $vendor, string $code, string $clientReference): Merchant
    {
        return $this->merchantCall($vendor, $code, function (Merchant $merchant) use ($clientReference) {
            return $this->provider->sendOtp([
                'merchantId' => $merchant->provider_merchant_id,
                'merchantRefId' => $clientReference,
                'pipe' => $merchant->pipe,
            ]);
        });
    }

    public function resendOtp(Vendor $vendor, string $code, string $clientReference): Merchant
    {
        return $this->merchantCall($vendor, $code, function (Merchant $merchant) use ($clientReference) {
            return $this->provider->resendOtp([
                'merchantId' => $merchant->provider_merchant_id,
                'merchantRefId' => $clientReference,
                'pipe' => $merchant->pipe,
            ]);
        });
    }

    public function verifyOtp(Vendor $vendor, string $code, string $clientReference, string $otp): Merchant
    {
        return $this->merchantCall($vendor, $code, function (Merchant $merchant) use ($clientReference, $otp) {
            return $this->provider->verifyOtp([
                'merchantId' => $merchant->provider_merchant_id,
                'merchantRefId' => $clientReference,
                'otp' => $otp,
                'pipe' => $merchant->pipe,
            ]);
        });
    }

    public function ekyc(Vendor $vendor, string $code, string $clientReference, string $pidData): Merchant
    {
        return $this->merchantCall($vendor, $code, function (Merchant $merchant) use ($clientReference, $pidData) {
            return $this->provider->ekyc([
                'merchantId' => $merchant->provider_merchant_id,
                'merchantRefId' => $clientReference,
                'pipe' => $merchant->pipe,
                'pidData' => $pidData,
            ]);
        });
    }

    public function twoFactor(Vendor $vendor, string $code, array $input): Merchant
    {
        return $this->merchantCall($vendor, $code, function (Merchant $merchant) use ($input) {
            $result = $this->provider->twoFactor([
                'merchantId' => $merchant->provider_merchant_id,
                'merchantRefId' => $input['client_reference'],
                'aadhaarNumber' => $input['aadhaar'],
                'pipe' => $merchant->pipe,
                'deviceType' => $input['device_type'],
                'pidData' => $input['pid_data'],
                'lat' => $input['lat'],
                'long' => $input['long'],
            ]);
            if (($result['provider_status_code'] ?? null) === '000') {
                $merchant->two_fa_at = now();
            }

            return $result;
        });
    }

    public function transactionOtp(Vendor $vendor, array $input): AepsTransaction
    {
        $service = strtoupper($input['service']);
        if (! in_array($service, ['CWTFA', 'APTFA'], true)) {
            throw new AepsException('Transaction OTP type must be CWTFA or APTFA.');
        }

        return $this->persistTransaction($vendor, $input, $service, function () use ($input, $vendor) {
            $merchant = $this->merchantForVendor($vendor, $input['merchant']);

            return $this->provider->transactionOtp([
                'merchantRefId' => $input['client_reference'],
                'merchantId' => $merchant->provider_merchant_id,
                'bankIIN' => $input['bank_iin'],
                'aadhaarNumber' => $input['aadhaar'],
                'transactionType' => strtoupper($input['service']),
                'amount' => $this->amountString($input['amount']),
                'mobileNumber' => $input['mobile'],
                'custMobileNumber' => $input['customer_mobile'] ?? '',
                'lat' => $input['lat'],
                'long' => $input['long'],
                'ipAddress' => $input['ip_address'],
                'pipe' => $merchant->pipe,
                'appPlatform' => $input['app_platform'],
                'appVersion' => $input['app_version'],
            ]);
        });
    }

    public function transact(Vendor $vendor, array $input): AepsTransaction
    {
        $service = strtoupper($input['service']);
        $this->assertTransactionAmount($service, (float) $input['amount']);
        $merchant = $this->merchantForVendor($vendor, $input['merchant']);
        $input['cw_auth_txn_id'] = $this->cwAuthTxnId($vendor, $merchant, $service, $input) ?? '';

        return $this->persistTransaction($vendor, $input, $service, function () use ($input, $service, $vendor) {
            $merchant = $this->merchantForVendor($vendor, $input['merchant']);
            $payload = [
                'merchantRefId' => $input['client_reference'],
                'merchantId' => $merchant->provider_merchant_id,
                'transactionType' => $service,
                'aadhaarNumber' => $input['aadhaar'],
                'mobileNumber' => $input['mobile'],
                'amount' => $this->amountString($input['amount']),
                'bankIIN' => $input['bank_iin'],
                'ipAddress' => $input['ip_address'],
                'pipe' => $merchant->pipe,
                'lat' => $input['lat'],
                'long' => $input['long'],
                'deviceType' => $input['device_type'],
                'udf1' => $input['udf1'] ?? '',
                'udf2' => $input['udf2'] ?? '',
                'udf3' => $input['udf3'] ?? '',
                'pidData' => $input['pid_data'],
            ];
            if (! empty($input['cw_auth_txn_id'])) {
                $payload['cwAuthTxnId'] = $input['cw_auth_txn_id'];
            }

            return $this->provider->transact($payload);
        });
    }

    public function findTransaction(Vendor $vendor, string $reference): AepsTransaction
    {
        $txn = AepsTransaction::query()
            ->where('vendor_id', $vendor->id)
            ->where('reference', $reference)
            ->first();
        if (! $txn) {
            throw new AepsException('Transaction not found.', 404);
        }

        return $txn;
    }

    private function persistTransaction(Vendor $vendor, array $input, string $service, callable $call): AepsTransaction
    {
        $merchant = $this->merchantForVendor($vendor, $input['merchant']);
        if (! $merchant->provider_merchant_id) {
            throw new AepsException('Merchant is not registered with the AePS provider.');
        }
        $hash = AepsRequestFingerprint::transaction($vendor->id, $merchant->id, $service, $input);

        $existing = AepsTransaction::query()
            ->where('vendor_id', $vendor->id)
            ->where('client_reference', $input['client_reference'])
            ->first();
        if ($existing) {
            return $this->replay($existing, $hash);
        }

        try {
            $txn = AepsTransaction::create([
                'vendor_id' => $vendor->id,
                'merchant_id' => $merchant->id,
                'reference' => 'AEP-'.strtoupper(Str::random(12)),
                'client_reference' => $input['client_reference'],
                'request_hash' => $hash,
                'service' => $service,
                'amount' => $input['amount'],
                'status' => AepsStatus::INITIATED,
                'aadhaar_masked' => AepsPayloadSanitizer::maskDigits($input['aadhaar']),
                'bank_iin' => $input['bank_iin'] ?? null,
                'wallet_effect' => AepsWalletRule::PENDING,
                'charge_effect' => AepsWalletRule::PENDING,
            ]);
        } catch (QueryException) {
            $existing = AepsTransaction::query()
                ->where('vendor_id', $vendor->id)
                ->where('client_reference', $input['client_reference'])
                ->first();
            if ($existing) {
                return $this->replay($existing, $hash);
            }
            throw new AepsException('Transaction could not be stored.', 409);
        }

        $txn->update(['status' => AepsStatus::PROCESSING]);

        try {
            $result = $call();
        } catch (AepsException $e) {
            $txn->update([
                'status' => AepsStatus::PROVIDER_ERROR,
                'failure_reason' => 'Provider request failed',
            ]);
            throw $e;
        }

        $mapped = AepsStatus::fromProviderCode($result['provider_status_code'] ?? null) ?? AepsStatus::PROVIDER_ERROR;
        $txn->update([
            'status' => $mapped,
            'provider_status_code' => $result['provider_status_code'] ?? null,
            'provider_txn_ref' => $result['provider_txn_ref'] ?? null,
            'rrn' => $result['rrn'] ?? null,
            'npci_code' => $result['npci_code'] ?? null,
            'npci_message' => $result['npci_message'] ?? null,
            'provider_merchant_status' => $result['provider_merchant_status'] ?? null,
            'provider_status_description' => $result['provider_status_description'] ?? null,
            'provider_available_balance' => $result['provider_available_balance'] ?? null,
            'provider_transaction_list' => $result['transaction_list'] ?? null,
            'sanitized_response' => $result['sanitized'] ?? null,
            'failure_reason' => in_array($mapped, [AepsStatus::FAILED, AepsStatus::VALIDATION_FAILED], true)
                ? ($result['provider_status_description'] ?? $result['message'] ?? null)
                : null,
            'wallet_effect' => AepsWalletRule::PENDING,
            'charge_effect' => AepsWalletRule::PENDING,
        ]);

        return $txn->fresh();
    }

    private function replay(AepsTransaction $existing, string $hash): AepsTransaction
    {
        if (! hash_equals($existing->request_hash, $hash)) {
            throw new AepsException('This reference was already used with different transaction details.', 409);
        }

        return $existing;
    }

    private function merchantCall(Vendor $vendor, string $code, callable $call): Merchant
    {
        $merchant = $this->merchantForVendor($vendor, $code);
        if (! $merchant->provider_merchant_id) {
            throw new AepsException('Merchant is not registered with the AePS provider.');
        }
        $result = $call($merchant);
        $this->applyProviderResult($merchant, $result);
        $merchant->save();

        return $merchant->fresh();
    }

    private function applyProviderResult(Merchant $merchant, array $result): void
    {
        if (! empty($result['provider_merchant_id'])) {
            $merchant->provider_merchant_id = $result['provider_merchant_id'];
        }
        if (! empty($result['provider_txn_ref'])) {
            $merchant->provider_ref = $result['provider_txn_ref'];
        }
        $merchant->provider_status_code = $result['provider_status_code'] ?? $merchant->provider_status_code;
        $merchant->provider_status_description = $result['provider_status_description'] ?? null;
        $merchant->onboarding_status = $result['status'] ?? $merchant->onboarding_status ?? AepsStatus::PROCESSING;
        $merchant->sanitized_response = $result['sanitized'] ?? null;
    }

    private function registrationPayload(array $input): array
    {
        return [
            'merchantRefId' => $input['client_reference'],
            'ipAddress' => $input['ip_address'],
            'lat' => $input['lat'],
            'long' => $input['long'],
            'firstName' => $input['first_name'],
            'lastName' => $input['last_name'],
            'middleName' => $input['middle_name'] ?? '',
            'dob' => $input['dob'],
            'merchantPhoneNumber' => $input['phone'],
            'merchantAddress1' => $input['address1'],
            'merchantAddress2' => $input['address2'] ?? '',
            'merchantState' => $input['state'],
            'merchantDistrict' => $input['district'],
            'gender' => $input['gender'],
            'shopName' => $input['shop_name'],
            'motherName' => $input['mother_name'],
            'montherName' => $input['mother_name'],
            'fatherName' => $input['father_name'],
            'maritalStatus' => $input['marital_status'],
            'mcc' => $input['mcc'],
            'deviceIP' => $input['device_ip'],
            'merchantPinCode' => $input['pin_code'],
            'emailId' => $input['email'],
            'merchantPan' => $input['pan'],
            'aadhaarNumber' => $input['aadhaar'],
            'shopPan' => $input['shop_pan'],
            'bankAccountNumber' => $input['bank_account'],
            'bankIfscCode' => $input['bank_ifsc'],
            'bankName' => $input['bank_name'],
            'accountType' => $input['account_type'] ?? '',
            'shopAddress' => $input['shop_address'],
            'shopDistrict' => $input['shop_district'],
            'shopState' => $input['shop_state'],
            'shopPinCode' => $input['shop_pin'],
            'shopPincode' => $input['shop_pin'],
            'shopLat' => $input['shop_lat'],
            'shopLong' => $input['shop_long'],
            'merchantdistrict' => $input['district'],
            'pipe' => (string) $input['pipe'],
        ];
    }

    private function assertTransactionAmount(string $service, float $amount): void
    {
        if (! in_array($service, ['CW', 'BE', 'MS', 'AP', 'CD'], true)) {
            throw new AepsException('Invalid AePS transaction type.');
        }
        if (in_array($service, ['BE', 'MS'], true)) {
            if ($amount != 0.0) {
                throw new AepsException('Balance enquiry and mini statement amount must be 0.');
            }

            return;
        }
        if ($amount < 100 || $amount > 10000) {
            throw new AepsException('Transaction amount must be between 100 and 10000.');
        }
    }

    private function amountString(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * VimoPay v1.0.13 marks cwAuthTxnId mandatory on the sale body and describes its source as
     * the txnRefId from the transaction OTP API. That OTP API is specified only for CW and AP
     * amounts above Rs 5000. Below that threshold, and for BE/MS/CD, the PDF does not give a
     * source value, so this method does not invent one. A client-supplied value is forwarded.
     * Amounts above 5000 must match a stored OTP txnRefId for this merchant.
     */
    private function cwAuthTxnId(Vendor $vendor, Merchant $merchant, string $service, array $input): ?string
    {
        $given = trim((string) ($input['cw_auth_txn_id'] ?? ''));
        $otpRequired = in_array($service, ['CW', 'AP'], true) && (float) $input['amount'] > 5000;

        if (! $otpRequired) {
            return $given === '' ? null : $given;
        }

        if ($given === '') {
            throw new AepsException('cw_auth_txn_id is required for CW and AP amounts above 5000. Use the txnRefId returned by the transaction OTP API.');
        }

        $otp = AepsTransaction::query()
            ->where('vendor_id', $vendor->id)
            ->where('merchant_id', $merchant->id)
            ->where('service', $service === 'CW' ? 'CWTFA' : 'APTFA')
            ->where('provider_txn_ref', $given)
            ->where('provider_status_code', '000')
            ->first();

        if (! $otp) {
            throw new AepsException('cw_auth_txn_id does not match a successful transaction OTP reference for this merchant.');
        }

        return $given;
    }

    private function replayMerchant(Merchant $existing, string $hash): Merchant
    {
        if (! is_string($existing->registration_hash) || ! hash_equals($existing->registration_hash, $hash)) {
            throw new AepsException('This reference was already used with different merchant details.', 409);
        }

        return $existing;
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        $state = (string) ($exception->errorInfo[0] ?? '');

        return in_array($state, ['23000', '23505'], true);
    }

    private function merchantForVendor(Vendor $vendor, string $code): Merchant
    {
        $merchant = Merchant::query()
            ->where('vendor_id', $vendor->id)
            ->where('code', $code)
            ->first();
        if (! $merchant) {
            throw new AepsException('Merchant not found.', 404);
        }

        return $merchant;
    }

    private function findMerchantByClientReference(Vendor $vendor, string $reference): ?Merchant
    {
        return Merchant::query()
            ->where('vendor_id', $vendor->id)
            ->where('client_reference', $reference)
            ->first();
    }
}
