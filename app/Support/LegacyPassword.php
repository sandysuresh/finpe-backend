<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use RuntimeException;

class LegacyPassword
{
    public static function normalize(?string $hash): string
    {
        $hash = (string) $hash;

        if (str_starts_with($hash, '$2a$')) {
            return '$2y$'.substr($hash, 4);
        }

        return $hash;
    }

    public static function accepted(string $hash): bool
    {
        return str_starts_with($hash, '$2y$')
            && (password_get_info($hash)['algoName'] ?? '') === 'bcrypt';
    }

    public static function make(string $plain): string
    {
        $hash = Hash::make($plain);

        if (! self::accepted($hash) || ! Hash::check($plain, $hash)) {
            throw new RuntimeException('Password could not be stored in bcrypt format.');
        }

        return $hash;
    }

    public static function check(string $plain, ?string $hash): bool
    {
        $hash = self::normalize($hash);

        if ($hash === '') {
            return false;
        }

        try {
            return Hash::check($plain, $hash);
        } catch (RuntimeException) {
            return false;
        }
    }
}
