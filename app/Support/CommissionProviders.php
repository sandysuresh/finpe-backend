<?php

namespace App\Support;

class CommissionProviders
{
    public const VIMOPAY = 'vimopay';

    public const ALLIANCEPE = 'alliancepe';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::VIMOPAY => 'VimoPay',
            self::ALLIANCEPE => 'AlliancePe',
        ];
    }

    public static function name(?string $code): ?string
    {
        $code = strtolower(trim((string) $code));
        if ($code === '') {
            return null;
        }

        return self::options()[$code] ?? $code;
    }
}
