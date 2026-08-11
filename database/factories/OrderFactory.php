<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'plan_id' => Plan::factory(),
            'price' => '50000',
            'status' => 'pending',
            'invoice_token' => Str::random(32),
            'admin_id' => 0,
        ];
    }
}
