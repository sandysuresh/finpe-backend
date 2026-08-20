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
        if ($token === null || $token === '') {
            return null;
        }

        // Sequential numeric IDs are never accepted in URLs.
        if (ctype_digit($token)) {
            return null;
        }

        try {
            $padded = strtr($token, '-_', '+/');
            $pad = strlen($padded) % 4;
            if ($pad > 0) {
                $padded .= str_repeat('=', 4 - $pad);
            }

            $plain = Crypt::decryptString($padded);

            if (! ctype_digit($plain)) {
                return null;
            }

            return (int) $plain;
        } catch (DecryptException) {
            return null;
        }
    }
}
