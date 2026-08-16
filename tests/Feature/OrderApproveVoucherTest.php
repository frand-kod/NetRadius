<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\Hotspot\HotspotDeviceInterface;
use App\Services\Hotspot\RadiusRestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApproveVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_as_paid_route_binds_by_id(): void
    {
        $admin = User::factory()->create(['status' => 'Active']);

        $fake = new class implements HotspotDeviceInterface
        {
            public function addCustomer(Customer $c, Plan $p): void {}

            public function removeCustomer(Customer $c, Plan $p): void {}

            public function syncCustomer(Customer $c, Plan $p): void {}

            public function changeUsername(Plan $p, string $f, string $t): void {}

            public function addPlan(Plan $p): void {}

            public function updatePlan(Plan $o, Plan $n): void {}

            public function removePlan(Plan $p): void {}

            public function onlineCustomer(Customer $c, string $r): ?string
            {
                return null;
            }

            public function connectCustomer(Customer $c, string $ip, string $mac, string $r): void {}

            public function disconnectCustomer(Customer $c, string $r): void {}
        };
        $this->app->instance(RadiusRestService::class, $fake);

        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        // Approve by numeric id (Order's route key is invoice_token by default) — must not 404.
        $resp = $this->actingAs($admin, 'web')
            ->post(route('admin.orders.mark-as-paid', $order->id));

        $this->assertNotSame(404, $resp->status(), 'mark-as-paid returned 404');
        $this->assertSame('paid', $order->fresh()->status);
    }
}
