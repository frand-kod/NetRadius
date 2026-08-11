<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_with_correct_plaintext_password(): void
    {
        $customer = Customer::factory()->create(['password' => 'secret123', 'status' => 'Active']);

        $response = $this->post('/customer/login', [
            'username' => $customer->username,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertTrue(Auth::guard('customer')->id() === $customer->id);
    }

    public function test_customer_cannot_login_with_wrong_password(): void
    {
        Customer::factory()->create(['username' => 'johndoe', 'password' => 'secret123', 'status' => 'Active']);

        $response = $this->post('/customer/login', [
            'username' => 'johndoe',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_inactive_customer_cannot_login(): void
    {
        Customer::factory()->create(['username' => 'inactiveuser', 'password' => 'secret123', 'status' => 'Inactive']);

        $response = $this->post('/customer/login', [
            'username' => 'inactiveuser',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_authenticated_customer_can_view_dashboard(): void
    {
        $customer = Customer::factory()->create(['status' => 'Active']);

        $response = $this->actingAs($customer, 'customer')->get('/customer/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Customer/Dashboard')
            ->where('customer.fullname', $customer->fullname));
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get('/customer/dashboard');

        $response->assertRedirect(route('customer.login'));
    }

    public function test_admin_guard_is_unaffected_by_customer_guard(): void
    {
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('customer')->check());
    }
}
