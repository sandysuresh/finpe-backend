<?php

namespace App\Services\Aeps;

/**
 * Deterministic fingerprints for idempotency.
 * The stored value is a SHA-256 digest. Raw Aadhaar, PAN, account numbers, and PID XML are not returned.
 */
class AepsRequestFingerprint
{
    public static function transaction(int $vendorId, int $merchantId, string $service, array $input): string
    {
        return hash('sha256', implode('|', [
            $vendorId,
            $merchantId,
            strtoupper(trim($service)),
            self::text($input['client_reference'] ?? ''),
            self::text($input['merchant'] ?? ''),
            self::amount($input['amount'] ?? '0'),
            self::text($input['bank_iin'] ?? ''),
            self::secret($input['aadhaar'] ?? ''),
            self::text($input['mobile'] ?? ''),
            self::text($input['device_type'] ?? ''),
            self::text($input['lat'] ?? ''),
            self::text($input['long'] ?? ''),
            self::text($input['cw_auth_txn_id'] ?? ''),
            self::text($input['customer_mobile'] ?? ''),
            self::text($input['app_platform'] ?? ''),
            self::text($input['app_version'] ?? ''),
            self::pid($input['pid_data'] ?? ''),
        ]));
    }

    public static function registration(int $vendorId, array $input): string
    {
        $parts = [$vendorId];
        foreach ([
            'client_reference', 'pipe', 'first_name', 'middle_name', 'last_name', 'dob', 'email', 'phone',
            'address1', 'address2', 'state', 'district', 'gender', 'shop_name', 'mother_name', 'father_name',
            'marital_status', 'mcc', 'device_ip', 'pin_code', 'bank_ifsc', 'bank_name', 'account_type',
            'shop_address', 'shop_district', 'shop_state', 'shop_pin', 'shop_lat', 'shop_long', 'lat', 'long', 'ip_address',
        ] as $key) {
            $parts[] = self::text($input[$key] ?? '');
        }
        $parts[] = self::secret($input['aadhaar'] ?? '');
        $parts[] = self::secret($input['pan'] ?? '');
        $parts[] = self::secret($input['shop_pan'] ?? '');
        $parts[] = self::secret($input['bank_account'] ?? '');

        return hash('sha256', implode('|', $parts));
    }

    /**
     * Biometric identity is the PID Data and Hmac elements.
     * Capture scores and timestamps in the sample PID are not part of the fingerprint.
     */
    public static function pid(string $pid): string
    {
        $pid = trim($pid);
        if ($pid === '') {
            return hash('sha256', '');
        }

        $data = self::xmlText($pid, 'Data');
        $hmac = self::xmlText($pid, 'Hmac');
        if ($data === null && $hmac === null) {
            return hash('sha256', $pid);
        }

        return hash('sha256', ($hmac ?? '').'|'.($data ?? ''));
    }

    private static function xmlText(string $xml, string $tag): ?string
    {
        $pattern = '/<'.$tag.'\b[^>]*>(.*?)<\/'.$tag.'>/s';
        if (preg_match($pattern, $xml, $match) !== 1) {
            return null;
        }

        $text = trim($match[1]);

        return $text === '' ? null : $text;
    }

    private static function secret(string $value): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');

        return hash('sha256', $normalized);
    }

    private static function text(mixed $value): string
    {
        return trim((string) $value);
    }

    private static function amount(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
