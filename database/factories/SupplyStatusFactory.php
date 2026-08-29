<?php

namespace Database\Factories;

use App\Models\SupplyStatus;
use App\Models\User;
use App\SupplyLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplyStatus>
 */
class SupplyStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location' => 'Kantor Dukcapil Limboto',
            'item' => fake()->unique()->randomElement(['Blangko KTP-el', 'Ribbon KTP-el', 'Blangko KIA', 'Ribbon KIA']),
            'status' => SupplyLevel::Available,
            'note' => fake()->sentence(),
            'source' => 'Pembaruan petugas Dukcapil',
            'checked_at' => now(),
            'updated_by' => User::factory()->superAdmin(),
        ];
    }
}
