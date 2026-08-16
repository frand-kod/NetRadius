<?php

namespace Tests\Feature;

use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\UserRecharge;
use App\Services\Hotspot\HotspotDeviceInterface;
use App\Services\Hotspot\RadiusRestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckExpiredPlansTest extends TestCase
{
    use RefreshDatabase;

    private array $removedCustomers = [];

    private function fakeDevice(): void
    {
        $this->removedCustomers = [];
        $removed = &$this->removedCustomers;

        $fake = new class($removed) implements HotspotDeviceInterface
        {
            public function __construct(private array &$removed) {}

            public function addCustomer(Customer $customer, Plan $plan): void {}

            public function removeCustomer(Customer $customer, Plan $plan): void
            {
                $this->removed[] = $customer->username;
            }

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

        $this->app->instance(RadiusRestService::class, $fake);
    }

    private function configureGowa(): void
    {
        AppConfig::set('alt_wga_server_url', 'http://127.0.0.1:3030');
        AppConfig::set('alt_wga_device_id', 'DEVICE1');
        AppConfig::set('alt_wga_username', 'admin');
        AppConfig::set('alt_wga_password', 'secret');
        AppConfig::set('country_code_phone', '62');
    }

    public function test_expired_plan_is_disabled_on_device_and_status_updated(): void
    {
        Http::fake();
        $this->fakeDevice();
        $this->configureGowa();

        $customer = Customer::factory()->create(['phonenumber' => '081234567890']);
        $plan = Plan::factory()->create();
        $recharge = UserRecharge::factory()->create([
            'customer_id' => $customer->id,
            'username' => $customer->username,
            'plan_id' => $plan->id,
            'status' => 'on',
            'expiration' => now()->subDay()->toDateString(),
            'time' => now()->subDay()->toTimeString(),
        ]);

        $this->artisan('app:check-expired-plans')->assertSuccessful();

        $this->assertSame('off', $recharge->fresh()->status);
        $this->assertContains($customer->username, $this->removedCustomers);
        Http::assertSent(fn ($request) => str_contains($request->url(), '127.0.0.1:3030/send/message'));
    }

    public function test_still_active_plan_is_not_touched(): void
    {
        Http::fake();
        $this->fakeDevice();
        $this->configureGowa();

        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();
        $recharge = UserRecharge::factory()->create([
            'customer_id' => $customer->id,
            'username' => $customer->username,
            'plan_id' => $plan->id,
            'status' => 'on',
            'expiration' => now()->addDays(10)->toDateString(),
            'time' => now()->toTimeString(),
        ]);

        $this->artisan('app:check-expired-plans')->assertSuccessful();

        $this->assertSame('on', $recharge->fresh()->status);
        $this->assertEmpty($this->removedCustomers);
    }

    public function test_reminder_sent_for_plan_expiring_in_exactly_one_day(): void
    {
        Http::fake();
        $this->fakeDevice();
        $this->configureGowa();

        $customer = Customer::factory()->create(['phonenumber' => '081234567890']);
        $plan = Plan::factory()->create();
        UserRecharge::factory()->create([
            'customer_id' => $customer->id,
            'username' => $customer->username,
            'plan_id' => $plan->id,
            'status' => 'on',
            'expiration' => now()->addDay()->toDateString(),
            'time' => now()->toTimeString(),
        ]);

        $this->artisan('app:check-expired-plans')->assertSuccessful();

        Http::assertSent(fn ($request) => str_contains($request->url(), '127.0.0.1:3030/send/message'));
    }

    public function test_reminder_skipped_for_voucher_recharge(): void
    {
        Http::fake();
        $this->fakeDevice();
        $this->configureGowa();

        $plan = Plan::factory()->create();
        UserRecharge::factory()->create([
            'customer_id' => 0,
            'username' => 'VOUCH01',
            'plan_id' => $plan->id,
            'status' => 'on',
            'expiration' => now()->addDay()->toDateString(),
            'time' => now()->toTimeString(),
        ]);

        $this->artisan('app:check-expired-plans')->assertSuccessful();

        Http::assertNothingSent();
    }
}
