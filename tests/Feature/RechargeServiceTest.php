<?php

namespace Tests\Feature;

use App\Events\CustomerRecharged;
use App\Exceptions\ActivePlanStillActiveException;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\UserRecharge;
use App\Services\Hotspot\HotspotDeviceInterface;
use App\Services\Hotspot\MikrotikHotspotService;
use App\Services\RechargeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RechargeServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeDevice(): HotspotDeviceInterface
    {
        $fake = new class implements HotspotDeviceInterface
        {
            public array $addedCalls = [];

            public function addCustomer(Customer $customer, Plan $plan): void
            {
                $this->addedCalls[] = [$customer, $plan];
            }

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

        return $fake;
    }

    public function test_new_recharge_creates_user_recharge_and_transaction_and_syncs_device(): void
    {
        Event::fake();
        $device = $this->fakeDevice();

        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();

        $transaction = app(RechargeService::class)->recharge($customer, $plan, $plan->routers, 'QR Payment', 'manual');

        $this->assertDatabaseHas('tbl_user_recharges', [
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'routers' => $plan->routers,
            'status' => 'on',
        ]);
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($plan->price, $transaction->price);
        $this->assertCount(1, $device->addedCalls);
        Event::assertDispatched(CustomerRecharged::class);
    }

    public function test_voucher_recharge_uses_zero_customer_id_and_zero_price(): void
    {
        $this->fakeDevice();
        $plan = Plan::factory()->create();

        $transaction = app(RechargeService::class)->recharge(null, $plan, $plan->routers, 'Voucher', 'VOUCH123');

        $this->assertDatabaseHas('tbl_user_recharges', [
            'customer_id' => 0,
            'username' => 'VOUCH123',
        ]);
        $this->assertEquals(0, $transaction->price);
        $this->assertEquals('VOUCH123', $transaction->username);
    }

    public function test_recharge_throws_when_active_plan_not_yet_expired(): void
    {
        $this->fakeDevice();
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();

        app(RechargeService::class)->recharge($customer, $plan, $plan->routers, 'QR Payment', 'manual');

        $this->expectException(ActivePlanStillActiveException::class);

        app(RechargeService::class)->recharge($customer, $plan, $plan->routers, 'QR Payment', 'manual');
    }

    public function test_recharge_reuses_expired_user_recharge_row_instead_of_creating_new(): void
    {
        $this->fakeDevice();
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create(['type' => 'Hotspot']);

        $expired = UserRecharge::factory()->create([
            'customer_id' => $customer->id,
            'routers' => $plan->routers,
            'type' => $plan->type,
            'expiration' => now()->subDay()->toDateString(),
            'time' => now()->subDay()->toTimeString(),
        ]);

        app(RechargeService::class)->recharge($customer, $plan, $plan->routers, 'QR Payment', 'manual');

        $this->assertSame(1, UserRecharge::count());
        $this->assertSame($expired->id, UserRecharge::first()->id);
        $this->assertSame('on', UserRecharge::first()->status);
    }
}
