<?php

namespace App\Exceptions;

use Exception;

class AepsException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 422,
    ) {
        parent::__construct($message);
    }
}
