<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\MessageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LogsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_tab_renders_with_data(): void
    {
        $admin = User::factory()->create(['status' => 'Active']);
        ActivityLog::create([
            'date' => now(),
            'type' => 'create',
            'description' => 'Plan #1 created',
            'userid' => $admin->id,
            'ip' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin, 'web')->get('/admin/logs?tab=activity');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Logs/Index')
            ->where('tab', 'activity')
            ->has('logs.data', 1));
    }

    public function test_message_log_tab_renders_with_data(): void
    {
        $admin = User::factory()->create(['status' => 'Active']);
        MessageLog::create([
            'message_type' => 'WhatsApp',
            'recipient' => '62812@s.whatsapp.net',
            'message_content' => 'Invoice #1',
            'status' => 'Success',
        ]);

        $response = $this->actingAs($admin, 'web')->get('/admin/logs?tab=message');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Logs/Index')
            ->where('tab', 'message')
            ->has('logs.data', 1));
    }

    public function test_logs_requires_admin_login(): void
    {
        $response = $this->get('/admin/logs');
        $response->assertRedirect(route('admin.login'));
    }
}
