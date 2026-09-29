<?php

namespace App\Services\Aeps;

/**
 * VimoPay AePS specification v1.0.13 status page.
 * 000 Success, 001 Failed, 002 Pending, 003 Validation Failed.
 */
final class AepsStatus
{
    public const SUCCESS = 'success';

    public const FAILED = 'failed';

    public const PENDING = 'pending';

    public const VALIDATION_FAILED = 'validation_failed';

    public const INITIATED = 'initiated';

    public const PROCESSING = 'processing';

    public const PROVIDER_ERROR = 'provider_error';

    public static function fromProviderCode(?string $code): ?string
    {
        return match ($code) {
            '000' => self::SUCCESS,
            '001' => self::FAILED,
            '002' => self::PENDING,
            '003' => self::VALIDATION_FAILED,
            default => null,
        };
    }
}
