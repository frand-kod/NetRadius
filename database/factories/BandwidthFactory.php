<?php

namespace Database\Factories;

use App\Models\Bandwidth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bandwidth>
 */
class BandwidthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_bw' => fake()->unique()->word(),
            'rate_down' => 2,
            'rate_down_unit' => 'Mbps',
            'rate_up' => 1,
            'rate_up_unit' => 'Mbps',
            'burst' => '',
        ];
    }
}
