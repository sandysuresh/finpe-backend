<?php

namespace App\Services\Aeps;

use RuntimeException;

/**
 * AES-GCM as described in the VimoPay specification encryption sample:
 * key and IV are the UTF-8 bytes supplied at partner onboarding, and the tag is appended.
 */
class VimoPayCipher
{
    public function encrypt(string $plain, string $key, string $iv): string
    {
        $tag = '';
        $cipher = $this->cipherName($key);
        $encrypted = openssl_encrypt($plain, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($encrypted === false || strlen($tag) !== 16) {
            throw new RuntimeException('Provider payload encryption failed.');
        }

        return base64_encode($encrypted.$tag);
    }

    public function decrypt(string $encoded, string $key, string $iv): string
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 17) {
            throw new RuntimeException('Provider payload decryption failed.');
        }

        $tag = substr($raw, -16);
        $ciphertext = substr($raw, 0, -16);
        $plain = openssl_decrypt($ciphertext, $this->cipherName($key), $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('Provider payload decryption failed.');
        }

        return $plain;
    }

    private function cipherName(string $key): string
    {
        return match (strlen($key)) {
            16 => 'aes-128-gcm',
            24 => 'aes-192-gcm',
            32 => 'aes-256-gcm',
            default => throw new RuntimeException('Provider encryption key length is not valid for AES-GCM.'),
        };
    }
}
