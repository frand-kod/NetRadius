<?php

namespace Tests\Feature;

use App\Events\CustomerRecharged;
use App\Events\OrderCreated;
use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\UserRecharge;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_send_telegram_calls_bot_api_and_logs_success(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        AppConfig::set('telegram_bot', 'BOTTOKEN');
        AppConfig::set('telegram_target_id', '12345');

        app(NotificationService::class)->sendTelegram('hello');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org/botBOTTOKEN/sendMessage')
            && $request['chat_id'] === '12345'
            && $request['text'] === 'hello');
        $this->assertDatabaseHas('tbl_message_logs', ['message_type' => 'Telegram', 'status' => 'Success']);
    }

    public function test_send_telegram_does_nothing_when_not_configured(): void
    {
        Http::fake();

        app(NotificationService::class)->sendTelegram('hello');

        Http::assertNothingSent();
        $this->assertDatabaseCount('tbl_message_logs', 0);
    }

    public function test_send_whatsapp_posts_to_gowa_with_auth_and_device_header(): void
    {
        Http::fake(['127.0.0.1:3030/*' => Http::response(['code' => 'SUCCESS', 'message' => 'Success'], 200)]);
        $this->configureGowa();

        app(NotificationService::class)->sendWhatsapp('081234567890', 'Halo dunia');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '127.0.0.1:3030/send/message')
                && $request->hasHeader('X-Device-Id', 'DEVICE1')
                && $request['phone'] === '6281234567890@s.whatsapp.net'
                && $request['message'] === 'Halo dunia'
                && $request->toPsrRequest()->getHeaderLine('Authorization') === 'Basic '.base64_encode('admin:secret');
        });
        $this->assertDatabaseHas('tbl_message_logs', ['message_type' => 'WhatsApp', 'status' => 'Success']);
    }

    public function test_send_whatsapp_does_nothing_when_not_configured(): void
    {
        Http::fake();

        app(NotificationService::class)->sendWhatsapp('081234567890', 'hello');

        Http::assertNothingSent();
        $this->assertDatabaseCount('tbl_message_logs', 0);
    }

    public function test_order_created_event_notifies_customer_and_admin(): void
    {
        Http::fake();
        AppConfig::set('telegram_bot', 'BOTTOKEN');
        AppConfig::set('telegram_target_id', '12345');
        $this->configureGowa();

        $customer = Customer::factory()->create(['phonenumber' => '081234567890']);
        $plan = Plan::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id, 'plan_id' => $plan->id]);

        event(new OrderCreated($order));

        Http::assertSentCount(2);
    }

    public function test_customer_recharged_event_notifies_customer_and_admin(): void
    {
        Http::fake();
        AppConfig::set('telegram_bot', 'BOTTOKEN');
        AppConfig::set('telegram_target_id', '12345');
        $this->configureGowa();

        $customer = Customer::factory()->create(['phonenumber' => '081234567890']);
        $plan = Plan::factory()->create();
        $transaction = Transaction::create([
            'invoice' => 'INV-1',
            'username' => $customer->username,
            'user_id' => $customer->id,
            'plan_name' => $plan->name_plan,
            'price' => $plan->price,
            'recharged_on' => now()->toDateString(),
            'recharged_time' => now()->toTimeString(),
            'expiration' => now()->addDays(30)->toDateString(),
            'time' => now()->toTimeString(),
            'method' => 'QR Payment - manual',
            'routers' => $plan->routers,
            'type' => $plan->type,
            'note' => '',
            'admin_id' => 1,
        ]);
        $userRecharge = UserRecharge::factory()->create([
            'customer_id' => $customer->id,
            'username' => $customer->username,
            'plan_id' => $plan->id,
        ]);

        event(new CustomerRecharged($transaction, $userRecharge, $plan, true));

        Http::assertSentCount(2);
    }
}
