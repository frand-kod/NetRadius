<?php

namespace Database\Factories;

use App\Models\Bandwidth;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_plan' => fake()->unique()->words(2, true),
            'id_bw' => Bandwidth::factory(),
            'price' => '50000',
            'type' => 'Hotspot',
            'typebp' => 'Unlimited',
            'validity' => 30,
            'validity_unit' => 'Days',
            'shared_users' => 1,
            'routers' => fn () => Router::factory()->create()->name,
            'device' => 'MikrotikHotspot',
            'enabled' => true,
        ];
    }
}
