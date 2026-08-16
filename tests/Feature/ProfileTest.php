<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_page_is_accessible(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass123')]);

        $this->actingAs($user, 'web')
            ->get('/admin/profile')
            ->assertOk();
    }

    public function test_admin_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass123')]);

        $this->actingAs($user, 'web')
            ->post('/admin/profile', [
                'fullname' => 'Nama Baru',
                'current_password' => 'oldpass123',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $user->fresh()->fullname);
        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_admin_partial_update_without_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass123')]);

        $this->actingAs($user, 'web')
            ->post('/admin/profile', [
                'fullname' => 'Nama Baru',
                'phone' => '',
                'email' => '',
                'current_password' => '',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $user->fresh()->fullname);
        $this->assertTrue(Hash::check('oldpass123', $user->fresh()->password));
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass123')]);

        $this->actingAs($user, 'web')
            ->post('/admin/profile', [
                'fullname' => 'Nama Baru',
                'current_password' => 'salah',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('oldpass123', $user->fresh()->password));
    }

    public function test_customer_password_is_stored_plaintext_after_change(): void
    {
        // Customer.password is plaintext (RADIUS PAP/CHAP needs the original value).
        $customer = Customer::factory()->create(['password' => 'oldpass123', 'status' => 'Active']);

        $this->actingAs($customer, 'customer')
            ->post('/customer/profile', [
                'fullname' => 'Nama Pelanggan',
                'current_password' => 'oldpass123',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('newpass123', $customer->fresh()->password);
    }
}
