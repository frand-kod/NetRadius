<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'status' => 'Active',
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_admin_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'status' => 'Active',
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest('web');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'status' => 'Inactive',
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest('web');
    }
}
