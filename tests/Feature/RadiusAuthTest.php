<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\UserRecharge;
use App\Models\Voucher;
use App\Services\Hotspot\HotspotDeviceInterface;
use App\Services\Hotspot\MikrotikHotspotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiusAuthTest extends TestCase
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

    public function test_authenticate_accepts_valid_pap_customer(): void
    {
        $customer = Customer::factory()->create(['password' => 'secret123', 'status' => 'Active']);

        $response = $this->postJson('/api/radius', [
            'action' => 'authenticate',
            'username' => $customer->username,
            'password' => 'secret123',
        ]);

        $response->assertStatus(204);
    }

    public function test_authenticate_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/radius', [
            'action' => 'authenticate',
            'username' => 'nobody',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_authorize_returns_reply_attributes_for_active_recharge(): void
    {
        $this->fakeDevice();
        $customer = Customer::factory()->create(['password' => 'secret123', 'status' => 'Active']);
        $plan = Plan::factory()->create(['type' => 'Hotspot', 'typebp' => 'Unlimited']);
        UserRecharge::factory()->create([
            'customer_id' => $customer->id,
            'username' => $customer->username,
            'plan_id' => $plan->id,
            'routers' => $plan->routers,
            'type' => $plan->type,
            'status' => 'on',
            'expiration' => now()->addDay()->toDateString(),
            'time' => now()->toTimeString(),
        ]);

        $response = $this->postJson('/api/radius', [
            'action' => 'authorize',
            'username' => $customer->username,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['reply:Mikrotik-Rate-Limit']);
    }

    public function test_authorize_activates_unused_voucher(): void
    {
        $this->fakeDevice();
        $plan = Plan::factory()->create(['type' => 'Hotspot', 'typebp' => 'Unlimited', 'routers' => 'radius']);
        $voucher = Voucher::factory()->create(['id_plan' => $plan->id, 'code' => 'VOUCH01', 'status' => '0']);

        $response = $this->postJson('/api/radius', [
            'action' => 'authorize',
            'username' => 'VOUCH01',
            'password' => 'VOUCH01',
        ]);

        $response->assertStatus(200);
        $this->assertSame('1', $voucher->fresh()->status);
        $this->assertDatabaseHas('tbl_user_recharges', ['username' => 'VOUCH01']);
    }

    public function test_authorize_rejects_when_no_recharge_and_not_a_voucher(): void
    {
        $customer = Customer::factory()->create(['password' => 'secret123', 'status' => 'Active']);

        $response = $this->postJson('/api/radius', [
            'action' => 'authorize',
            'username' => $customer->username,
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
    }

    public function test_unknown_action_is_rejected(): void
    {
        $response = $this->postJson('/api/radius', ['action' => 'post-auth']);

        $response->assertStatus(401);
        $response->assertJsonFragment(['Reply-Message' => 'Invalid Command : post-auth']);
    }
}
