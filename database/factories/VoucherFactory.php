<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'Hotspot',
            'routers' => 'radius',
            'id_plan' => Plan::factory(),
            'code' => fake()->unique()->bothify('VCH-####'),
            'status' => '0',
            'generated_by' => 0,
        ];
    }
}
