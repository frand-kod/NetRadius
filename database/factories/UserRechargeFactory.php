<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\UserRecharge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserRecharge>
 */
class UserRechargeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => 0,
            'username' => fake()->unique()->userName(),
            'plan_id' => Plan::factory(),
            'namebp' => fake()->words(2, true),
            'recharged_on' => now()->toDateString(),
            'recharged_time' => now()->toTimeString(),
            'expiration' => now()->addDays(30)->toDateString(),
            'time' => now()->toTimeString(),
            'status' => 'on',
            'method' => '',
            'routers' => fake()->domainWord(),
            'type' => 'Hotspot',
            'admin_id' => 0,
        ];
    }
}
