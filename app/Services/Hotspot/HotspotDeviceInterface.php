<?php

namespace App\Services\Hotspot;

use App\Models\Customer;
use App\Models\Plan;

interface HotspotDeviceInterface
{
    public function addCustomer(Customer $customer, Plan $plan): void;

    public function removeCustomer(Customer $customer, Plan $plan): void;

    public function syncCustomer(Customer $customer, Plan $plan): void;

    public function changeUsername(Plan $plan, string $from, string $to): void;

    public function addPlan(Plan $plan): void;

    public function updatePlan(Plan $oldPlan, Plan $newPlan): void;

    public function removePlan(Plan $plan): void;

    public function onlineCustomer(Customer $customer, string $routerName): ?string;

    public function connectCustomer(Customer $customer, string $ip, string $macAddress, string $routerName): void;

    public function disconnectCustomer(Customer $customer, string $routerName): void;
}
