<?php

namespace App\Exceptions;

use App\Models\UserRecharge;
use RuntimeException;

class ActivePlanStillActiveException extends RuntimeException
{
    public function __construct(public readonly UserRecharge $activeRecharge)
    {
        parent::__construct(
            "Customer already has an active plan [{$activeRecharge->namebp}] expiring at {$activeRecharge->expiration->toDateString()} {$activeRecharge->time}."
        );
    }
}
