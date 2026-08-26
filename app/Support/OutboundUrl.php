<?php

namespace App\Support;

use InvalidArgumentException;

class OutboundUrl
{
    public static function assertSafe(string $url, bool $allowPrivate = false): void
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 500) {
            throw new InvalidArgumentException('Invalid URL.');
        }

        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvalidArgumentException('URL must include a scheme and host.');
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (! in_array($scheme, ['https', 'http'], true)) {
            throw new InvalidArgumentException('Only http and https URLs are allowed.');
        }

        if (app()->environment('production') && $scheme !== 'https') {
            throw new InvalidArgumentException('HTTPS is required in production.');
        }

        $host = strtolower((string) $parts['host']);
        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            if (! $allowPrivate) {
                throw new InvalidArgumentException('Private or local hosts are not allowed.');
            }
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Host could not be resolved.');
        }

        if (! $allowPrivate && self::isBlockedIp($ip)) {
            throw new InvalidArgumentException('Private, loopback, or link-local hosts are not allowed.');
        }
    }

    public static function join(string $base, string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return rtrim($base, '/');
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $path) || str_starts_with($path, '//') || str_contains($path, '..')) {
            throw new InvalidArgumentException('Invalid API path.');
        }

        return rtrim($base, '/').'/'.ltrim($path, '/');
    }

    public static function isBlockedIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long === false) {
                return true;
            }

            $ranges = [
                ['0.0.0.0', 8],
                ['10.0.0.0', 8],
                ['127.0.0.0', 8],
                ['169.254.0.0', 16],
                ['172.16.0.0', 12],
                ['192.168.0.0', 16],
                ['224.0.0.0', 4],
            ];

            foreach ($ranges as [$net, $bits]) {
                $mask = -1 << (32 - $bits);
                if (($long & $mask) === (ip2long($net) & $mask)) {
                    return true;
                }
            }

            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $bin = inet_pton($ip);
            if ($bin === false) {
                return true;
            }
            $mapped = substr($bin, 0, 12) === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff";
            if ($mapped) {
                return self::isBlockedIp(inet_ntop(substr($bin, 12)));
            }

            return $ip === '::1'
                || str_starts_with($ip, 'fe80:')
                || str_starts_with($ip, 'fc')
                || str_starts_with($ip, 'fd');
        }

        return true;
    }
}
