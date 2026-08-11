<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Exceptions\ActivePlanStillActiveException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Services\Hotspot\HotspotDeviceInterface;
use App\Services\Hotspot\MikrotikHotspotService;
use App\Services\OrderService;
use App\Services\RechargeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function fakeDevice(): void
    {
        $fake = new class implements HotspotDeviceInterface
        {
            public function addCustomer(Customer $customer, Plan $plan): void {}

            public function removeCustomer(Customer $customer, Plan $plan): void {}

            public function syncCustomer(Customer $customer, Plan $plan): void {}

            public function changeUsername(Plan $plan, string $from, string $to): void {}

            public function addPlan(Plan $plan): void {}

            public function updatePlan(Plan $oldPlan, Plan $newPlan): void {}

            public function removePlan(Plan $plan): void {}

            public function onlineCustomer(Customer $customer, string $routerName): ?string
            {
                return null;
            }

            public function connectCustomer(Customer $customer, string $ip, string $macAddress, string $routerName): void {}

            public function disconnectCustomer(Customer $customer, string $routerName): void {}
        };

        $this->app->instance(MikrotikHotspotService::class, $fake);
    }

    public function test_order_service_creates_pending_order_and_dispatches_event(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create(['price' => '75000']);

        $order = app(OrderService::class)->create($customer, $plan, adminId: 1);

        $this->assertDatabaseHas('tbl_orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'price' => '75000',
            'status' => 'pending',
        ]);
        $this->assertNotEmpty($order->invoice_token);
        Event::assertDispatched(OrderCreated::class, fn ($event) => $event->order->is($order));
    }

    public function test_public_invoice_page_is_accessible_without_login(): void
    {
        $customer = Customer::factory()->create(['fullname' => 'Budi Santoso']);
        $plan = Plan::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id, 'plan_id' => $plan->id]);

        $response = $this->get(route('invoice.show', $order->invoice_token));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee($plan->name_plan);
    }

    public function test_marking_order_as_paid_recharges_customer_and_updates_status(): void
    {
        $this->fakeDevice();
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id, 'plan_id' => $plan->id, 'status' => 'pending']);

        app(OrderService::class)->markAsPaid($order, adminId: 1);

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertDatabaseHas('tbl_user_recharges', [
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
        ]);
    }

    public function test_marking_order_as_paid_fails_when_customer_has_active_plan(): void
    {
        $this->fakeDevice();
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();

        app(RechargeService::class)->recharge($customer, $plan, $plan->routers, 'QR Payment', 'manual');
        $order = Order::factory()->create(['customer_id' => $customer->id, 'plan_id' => $plan->id, 'status' => 'pending']);

        $this->expectException(ActivePlanStillActiveException::class);

        try {
            app(OrderService::class)->markAsPaid($order, adminId: 1);
        } finally {
            $this->assertSame('pending', $order->fresh()->status);
        }
    }
}
