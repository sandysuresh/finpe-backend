<?php

namespace App\Exceptions;

use RuntimeException;

class AlliancePeException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 422,
        public readonly ?string $errorCode = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }
}
