<?php

namespace Database\Factories;

use App\AdministrativeAreaLevel;
use App\Models\PopulationStatistic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PopulationStatistic>
 */
class PopulationStatisticFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(3, true),
            'category' => 'Penduduk',
            'value' => fake()->numberBetween(1_000, 500_000),
            'unit' => 'jiwa',
            'period' => 'Semester II 2025',
            'area' => 'Kabupaten Gorontalo',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
            'parent_area_code' => null,
            'source' => 'Dinas Dukcapil Kabupaten Gorontalo',
            'source_url' => 'https://dukcapil.gorontalokab.go.id/',
            'published_at' => now()->subDay(),
            'updated_by' => User::factory()->superAdmin(),
        ];
    }
}
