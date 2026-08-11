<?php

namespace Database\Factories;

use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Router>
 */
class RouterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->domainWord(),
            'ip_address' => fake()->localIpv4(),
            'username' => 'admin',
            'password' => fake()->password(),
            'description' => fake()->sentence(),
            'status' => 'Online',
            'enabled' => true,
        ];
    }
}
