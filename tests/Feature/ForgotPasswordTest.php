<?php

namespace Tests\Feature;

use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\User;
use App\Services\PasswordResetOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function configureGowa(): void
    {
        AppConfig::set('alt_wga_server_url', 'http://127.0.0.1:3030');
        AppConfig::set('alt_wga_device_id', 'DEVICE1');
        AppConfig::set('alt_wga_username', 'admin');
        AppConfig::set('alt_wga_password', 'secret');
        AppConfig::set('country_code_phone', '62');
    }

    public function test_customer_can_request_and_use_otp_to_reset_plaintext_password(): void
    {
        Http::fake(['127.0.0.1:3030/*' => Http::response(['code' => 'SUCCESS'], 200)]);
        $this->configureGowa();
        $customer = Customer::factory()->create(['username' => 'johndoe', 'phonenumber' => '081234567890', 'password' => 'oldpass']);

        app(PasswordResetOtpService::class)->requestOtp('customer', 'johndoe');
        Http::assertSent(fn ($request) => str_contains($request->url(), '127.0.0.1:3030/send/message'));

        $otp = Cache::get('forgot-password:customer:'.sha1('johndoe'))['otp'];
        $newPassword = app(PasswordResetOtpService::class)->verifyAndReset('customer', 'johndoe', $otp);

        $this->assertNotNull($newPassword);
        $this->assertSame($newPassword, $customer->fresh()->password);
    }

    public function test_wrong_otp_does_not_reset_password_and_decrements_attempts(): void
    {
        Http::fake();
        $this->configureGowa();
        $customer = Customer::factory()->create(['username' => 'janedoe', 'phonenumber' => '081234567890', 'password' => 'oldpass']);

        app(PasswordResetOtpService::class)->requestOtp('customer', 'janedoe');
        $result = app(PasswordResetOtpService::class)->verifyAndReset('customer', 'janedoe', '000000');

        $this->assertNull($result);
        $this->assertSame('oldpass', $customer->fresh()->password);
        $this->assertSame(4, Cache::get('forgot-password:customer:'.sha1('janedoe'))['attempts']);
    }

    public function test_otp_is_invalidated_after_max_failed_attempts(): void
    {
        Http::fake();
        $this->configureGowa();
        Customer::factory()->create(['username' => 'maxattempts', 'phonenumber' => '081234567890']);

        app(PasswordResetOtpService::class)->requestOtp('customer', 'maxattempts');

        for ($i = 0; $i < 5; $i++) {
            app(PasswordResetOtpService::class)->verifyAndReset('customer', 'maxattempts', '000000');
        }

        $this->assertNull(Cache::get('forgot-password:customer:'.sha1('maxattempts')));
    }

    public function test_admin_reset_stores_hashed_password(): void
    {
        Http::fake();
        $this->configureGowa();
        $admin = User::factory()->create(['username' => 'superadmin', 'phone' => '081234567890']);

        app(PasswordResetOtpService::class)->requestOtp('admin', 'superadmin');
        $otp = Cache::get('forgot-password:admin:'.sha1('superadmin'))['otp'];
        $newPassword = app(PasswordResetOtpService::class)->verifyAndReset('admin', 'superadmin', $otp);

        $this->assertNotNull($newPassword);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($newPassword, $admin->fresh()->password));
    }

    public function test_request_for_unknown_username_does_not_error_and_sends_nothing(): void
    {
        Http::fake();
        $this->configureGowa();

        app(PasswordResetOtpService::class)->requestOtp('customer', 'doesnotexist');

        Http::assertNothingSent();
    }

    public function test_full_http_flow_via_customer_routes(): void
    {
        Http::fake(['127.0.0.1:3030/*' => Http::response(['code' => 'SUCCESS'], 200)]);
        $this->configureGowa();
        Customer::factory()->create(['username' => 'httpflow', 'phonenumber' => '081234567890', 'password' => 'oldpass']);

        $this->post(route('customer.forgot-password.request'), ['username' => 'httpflow'])
            ->assertSessionHas('status');

        $otp = Cache::get('forgot-password:customer:'.sha1('httpflow'))['otp'];

        $this->post(route('customer.forgot-password.reset'), ['username' => 'httpflow', 'otp' => $otp])
            ->assertSessionHas('new_password');
    }
}
