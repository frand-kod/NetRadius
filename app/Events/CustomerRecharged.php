<?php

namespace App\Events;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\UserRecharge;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerRecharged
{
    use Dispatchable;

    public function __construct(
        public readonly Transaction $transaction,
        public readonly UserRecharge $userRecharge,
        public readonly Plan $plan,
        public readonly bool $isNew,
    ) {}
}
