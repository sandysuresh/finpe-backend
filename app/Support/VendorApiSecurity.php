<?php

namespace App\Support;

use Illuminate\Http\Request;

class VendorApiSecurity
{
    public const TIMESTAMP_WINDOW_SECONDS = 300;

    public static function canonicalString(Request $request, string $timestamp, string $nonce): string
    {
        $path = '/'.ltrim($request->path(), '/');
        $query = $request->getQueryString();
        if ($query) {
            $path .= '?'.$query;
        }

        $bodyHash = hash('sha256', $request->getContent() ?? '');

        return implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($request->method()),
            $path,
            $bodyHash,
        ]);
    }

    public static function signature(string $canonical, string $secret): string
    {
        return hash_hmac('sha256', $canonical, $secret);
    }

    public static function signaturesMatch(string $provided, string $expected): bool
    {
        $provided = strtolower(trim($provided));
        $expected = strtolower(trim($expected));

        if ($provided === '' || strlen($provided) !== strlen($expected)) {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    public static function ipAllowed(string $ip, array $whitelist): bool
    {
        $ip = trim($ip);
        if ($ip === '' || $whitelist === []) {
            return false;
        }

        foreach ($whitelist as $entry) {
            $entry = trim((string) $entry);
            if ($entry === '') {
                continue;
            }

            if (str_contains($entry, '/')) {
                if (self::ipInCidr($ip, $entry)) {
                    return true;
                }
                continue;
            }

            if (strcasecmp($ip, $entry) === 0) {
                return true;
            }
        }

        return false;
    }

    public static function parseWhitelist(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $ips = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $ips[] = $line;
        }

        return array_values(array_unique($ips));
    }

    public static function isValidWhitelistEntry(string $entry): bool
    {
        if (str_contains($entry, '/')) {
            [$subnet, $bits] = array_pad(explode('/', $entry, 2), 2, null);
            if (! is_numeric($bits)) {
                return false;
            }
            $bits = (int) $bits;
            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $bits >= 16 && $bits <= 32 && $subnet !== '0.0.0.0';
            }
            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $bits >= 32 && $bits <= 128 && $subnet !== '::';
            }

            return false;
        }

        return filter_var($entry, FILTER_VALIDATE_IP) !== false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = array_pad(explode('/', $cidr, 2), 2, null);
        if ($subnet === null || $mask === null || ! is_numeric($mask)) {
            return false;
        }

        $mask = (int) $mask;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($mask < 0 || $mask > 32) {
                return false;
            }
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = $mask === 0 ? 0 : (-1 << (32 - $mask));

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            if ($ipBin === false || $subnetBin === false || $mask < 0 || $mask > 128) {
                return false;
            }
            $bytes = intdiv($mask, 8);
            $bits = $mask % 8;
            if (substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
                return false;
            }
            if ($bits === 0) {
                return true;
            }
            $maskByte = (~(0xFF >> $bits)) & 0xFF;

            return (ord($ipBin[$bytes]) & $maskByte) === (ord($subnetBin[$bytes]) & $maskByte);
        }

        return false;
    }
}
