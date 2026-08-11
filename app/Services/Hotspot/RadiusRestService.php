<?php

namespace App\Services\Hotspot;

use App\Models\Customer;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class RadiusRestService implements HotspotDeviceInterface
{
    public function addCustomer(Customer $customer, Plan $plan): void
    {
        // FreeRADIUS reads tbl_customers/tbl_plans directly via the REST module;
        // no explicit provisioning call is needed here.
    }

    public function removeCustomer(Customer $customer, Plan $plan): void
    {
        if ($plan->typebp === 'Limited' && in_array($plan->limit_type, ['Data_Limit', 'Both_Limit'], true)) {
            DB::table('rad_acct')
                ->where('username', $customer->username)
                ->update(['acctinputoctets' => 0, 'acctoutputoctets' => 0]);
        }
    }

    public function syncCustomer(Customer $customer, Plan $plan): void
    {
        $this->addCustomer($customer, $plan);
    }

    public function changeUsername(Plan $plan, string $from, string $to): void
    {
        //
    }

    public function addPlan(Plan $plan): void
    {
        //
    }

    public function updatePlan(Plan $oldPlan, Plan $newPlan): void
    {
        //
    }

    public function removePlan(Plan $plan): void
    {
        //
    }

    public function onlineCustomer(Customer $customer, string $routerName): ?string
    {
        return null;
    }

    public function connectCustomer(Customer $customer, string $ip, string $macAddress, string $routerName): void
    {
        //
    }

    public function disconnectCustomer(Customer $customer, string $routerName): void
    {
        //
    }
}
