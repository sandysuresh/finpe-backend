<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class UrlId
{
    public static function encode(int|string|null $id): string
    {
        if ($id === null || $id === '') {
            return '';
        }

        $encrypted = Crypt::encryptString((string) $id);

        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }

    public static function decode(?string $token): ?int
    {
        $plain = self::decrypt($token);

        if ($plain === null || ! ctype_digit($plain)) {
            return null;
        }

        return (int) $plain;
    }

    public static function decodeString(?string $token): ?string
    {
        $plain = self::decrypt($token);

        if ($plain === null || $plain === '') {
            return null;
        }

        return $plain;
    }

    private static function decrypt(?string $token): ?string
    {
        if ($token === null || $token === '') {
            return null;
        }

        // Plain IDs and codes are never accepted in URLs.
        if (ctype_digit($token) || preg_match('/^VND-/i', $token) === 1) {
            return null;
        }

        try {
            $padded = strtr($token, '-_', '+/');
            $pad = strlen($padded) % 4;
            if ($pad > 0) {
                $padded .= str_repeat('=', 4 - $pad);
            }

            return Crypt::decryptString($padded);
        } catch (DecryptException) {
            return null;
        }
    }
}
