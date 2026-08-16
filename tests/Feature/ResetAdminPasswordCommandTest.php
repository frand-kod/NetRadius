<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetAdminPasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_resets_admin_password(): void
    {
        $user = User::factory()->create(['username' => 'admin', 'password' => 'oldpass123']);

        $this->artisan('admin:reset-password', ['username' => 'admin', 'password' => 'newpass123'])
            ->expectsOutputToContain('berhasil direset')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_fails_when_username_not_found(): void
    {
        $this->artisan('admin:reset-password', ['username' => 'nobody', 'password' => 'x'])
            ->expectsOutputToContain('tidak ditemukan')
            ->assertExitCode(1);
    }
}
