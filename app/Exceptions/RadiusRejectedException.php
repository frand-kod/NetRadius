<?php

namespace App\Exceptions;

use RuntimeException;

class RadiusRejectedException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $extraAttributes
     */
    public function __construct(
        string $message,
        public readonly int $httpCode = 401,
        public readonly array $extraAttributes = [],
    ) {
        parent::__construct($message);
    }
}
