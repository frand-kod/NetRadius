<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_plan_while_authenticated_as_admin_writes_a_log_entry(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'web');

        $plan = Plan::factory()->create();

        $this->assertDatabaseHas('tbl_logs', [
            'type' => 'create',
            'userid' => $admin->id,
        ]);
        $this->assertDatabaseHas('tbl_logs', [
            'description' => "Plan #{$plan->id} created",
        ]);
    }

    public function test_creating_a_plan_without_authenticated_admin_writes_no_log(): void
    {
        Plan::factory()->create();

        $this->assertDatabaseCount('tbl_logs', 0);
    }

    public function test_updating_and_deleting_a_router_writes_log_entries(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'web');

        $router = \App\Models\Router::factory()->create();
        $router->update(['description' => 'updated']);
        $router->delete();

        $this->assertDatabaseHas('tbl_logs', ['type' => 'update', 'userid' => $admin->id]);
        $this->assertDatabaseHas('tbl_logs', ['type' => 'delete', 'userid' => $admin->id]);
    }

    public function test_admin_login_event_is_logged(): void
    {
        $admin = User::factory()->create();

        Auth::guard('web')->login($admin);

        $this->assertDatabaseHas('tbl_logs', ['type' => 'login', 'userid' => $admin->id]);
    }

    public function test_customer_login_does_not_write_admin_activity_log(): void
    {
        $customer = \App\Models\Customer::factory()->create();

        Auth::guard('customer')->login($customer);

        $this->assertDatabaseCount('tbl_logs', 0);
    }

    public function test_voucher_generation_writes_a_single_summary_log_entry(): void
    {
        $admin = User::factory()->create();
        $plan = Plan::factory()->create();

        app(\App\Services\VoucherService::class)->generate($plan, 5, 8, $admin->id);

        $this->assertDatabaseHas('tbl_logs', ['type' => 'generate', 'userid' => $admin->id]);
        $this->assertSame(1, \App\Models\ActivityLog::where('type', 'generate')->count());
    }
}
