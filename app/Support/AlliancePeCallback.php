<?php

namespace App\Support;

class AlliancePeCallback
{
    public const PATH = '/api/webhooks/alliancepe/payouts';

    public static function url(): ?string
    {
        $configured = trim((string) config('alliancepe.callback_url'));
        if (! str_starts_with($configured, 'https://')) {
            return null;
        }

        $path = parse_url($configured, PHP_URL_PATH);
        if ($path !== self::PATH) {
            return null;
        }

        return $configured;
    }
}
