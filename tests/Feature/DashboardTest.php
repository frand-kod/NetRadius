<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserRecharge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/admin')->assertOk();
    }

    public function test_dashboard_includes_recent_activity(): void
    {
        $user = User::factory()->create();
        Customer::factory()->create(['created_at' => now()]);

        $this->actingAs($user, 'web')
            ->get('/admin')
            ->assertInertia(fn ($page) => $page->component('Admin/Dashboard')
                ->has('recentActivities', 1));
    }

    public function test_dashboard_comparison_sent_as_arrays(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get('/admin')
            ->assertInertia(fn ($page) => $page->component('Admin/Dashboard')
                // Array (bukan object): item pertama diakses lewat indeks numerik.
                ->where('stats.comparison.customers.0.key', 'Hari Ini')
                ->where('stats.comparison.usage.0.key', 5));
    }

    public function test_realtime_endpoint_returns_usage_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/admin/dashboard/realtime')
            ->assertOk()
            ->assertJsonStructure(['usage', 'onlineUsers', 'expiring']);
    }

    public function test_realtime_lists_packages_expiring_soon(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name_plan' => 'Paket A']);
        UserRecharge::factory()->create([
            'username' => 'cust1',
            'plan_id' => $plan->id,
            'namebp' => 'Paket A',
            'status' => 'on',
            'expiration' => now()->addDays(2)->toDateString(),
            'time' => '12:00:00',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/admin/dashboard/realtime')
            ->assertOk()
            ->assertJsonPath('expiring.0.username', 'cust1')
            ->assertJsonPath('expiring.0.days_left', 2);
    }
}
