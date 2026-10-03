<?php

namespace App\Services\Aeps;

class AepsPayloadSanitizer
{
    public static function redact(mixed $value): mixed
    {
        if (! is_array($value)) {
            return is_string($value) ? self::clip($value) : $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            $name = strtolower((string) $key);
            if (self::isSecret($name)) {
                $out[$key] = '[redacted]';
                continue;
            }
            if (self::isAadhaar($name) && is_string($item)) {
                $out[$key] = self::maskDigits($item);
                continue;
            }
            if (self::isFinancialId($name)) {
                $out[$key] = '[redacted]';
                continue;
            }
            if (in_array($name, ['account'], true) && is_string($item) && strlen($item) > 4) {
                $out[$key] = str_repeat('*', max(0, strlen($item) - 4)).substr($item, -4);
                continue;
            }
            $out[$key] = self::redact($item);
        }

        return $out;
    }

    public static function maskDigits(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) < 4) {
            return '[redacted]';
        }

        return str_repeat('X', max(0, strlen($digits) - 4)).substr($digits, -4);
    }

    public static function displayAadhaar(?string $stored): string
    {
        if ($stored === null || trim($stored) === '' || $stored === '[redacted]') {
            return '—';
        }

        $digits = preg_replace('/\D+/', '', $stored) ?? '';
        if (strlen($digits) < 4) {
            return 'XXXX-XXXX-XXXX';
        }

        return 'XXXX-XXXX-'.substr($digits, -4);
    }

    public static function maskPhone(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) < 4) {
            return '[redacted]';
        }

        return str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }

    private static function isSecret(string $name): bool
    {
        if (in_array($name, [
            'password', 'secret', 'api_secret', 'secret_key', 'secretkey', 'api_key', 'salt', 'saltkey',
            'username', 'authorization', 'token', 'accesstoken', 'access_token', 'pid', 'piddata', 'otp',
            'biometric', 'wadh', 'skey', 'hmac', 'requestbody', 'encryptdecryptkey', 'encrypt_key', 'iv', 'iv_key',
        ], true)) {
            return true;
        }

        return str_contains($name, 'password')
            || str_contains($name, 'secret')
            || str_contains($name, 'piddata')
            || str_contains($name, 'biometric');
    }

    private static function isAadhaar(string $name): bool
    {
        return str_contains($name, 'aadhaar') || str_contains($name, 'aadhar');
    }

    private static function isFinancialId(string $name): bool
    {
        return in_array($name, [
            'pan', 'shop_pan', 'merchantpan', 'shoppan',
            'bank_account', 'bankaccountnumber', 'accountnumber', 'account_number',
        ], true);
    }

    private static function clip(string $value): string
    {
        if (str_contains($value, '<PidData') || str_contains($value, '<Skey')) {
            return '[redacted]';
        }

        return mb_substr($value, 0, 2000);
    }
}
