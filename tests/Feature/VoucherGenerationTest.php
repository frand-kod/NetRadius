<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_voucher_service_generates_unique_codes_for_radius(): void
    {
        $plan = Plan::factory()->create();

        $vouchers = app(VoucherService::class)->generate($plan, 20, 8, adminId: 1);

        $this->assertCount(20, $vouchers);
        $this->assertSame(20, $vouchers->pluck('code')->unique()->count());
        $this->assertTrue($vouchers->every(fn (Voucher $v) => $v->routers === 'radius' && $v->status === '0' && $v->id_plan === $plan->id));
        $this->assertDatabaseCount('tbl_voucher', 20);
    }

    public function test_print_route_requires_admin_login(): void
    {
        $response = $this->get('/admin/vouchers/print?ids=1,2');

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_print_route_shows_generated_voucher_codes(): void
    {
        $admin = User::factory()->create();
        $plan = Plan::factory()->create();
        $vouchers = app(VoucherService::class)->generate($plan, 3);

        $response = $this->actingAs($admin, 'web')->get('/admin/vouchers/print?ids='.$vouchers->pluck('id')->implode(','));

        $response->assertOk();
        foreach ($vouchers as $voucher) {
            $response->assertSee($voucher->code);
        }
    }
}
