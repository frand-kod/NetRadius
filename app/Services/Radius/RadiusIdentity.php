<?php

namespace App\Services\Radius;

class RadiusIdentity
{
    public function __construct(
        public string $username,
        public string $password,
        public bool $isVoucher,
        public bool $isChap,
    ) {}
}
