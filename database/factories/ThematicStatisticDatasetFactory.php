<?php

namespace Database\Factories;

use App\Models\ThematicStatisticDataset;
use App\Models\User;
use App\ThematicStatisticDatasetStatus;
use App\ThematicStatisticType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ThematicStatisticDataset> */
class ThematicStatisticDatasetFactory extends Factory
{
    protected $model = ThematicStatisticDataset::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type' => ThematicStatisticType::LaborForceParticipation,
            'period' => 'Semester I 2026',
            'source' => 'Dinas Dukcapil Kabupaten Gorontalo',
            'source_url' => 'https://dukcapil.gorontalokab.go.id/',
            'original_filename' => 'statistik.xlsx',
            'file_hash' => hash('sha256', fake()->uuid()),
            'status' => ThematicStatisticDatasetStatus::Draft,
            'row_count' => 1,
            'observation_count' => 1,
            'published_at' => null,
            'imported_by' => User::factory()->superAdmin(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ThematicStatisticDatasetStatus::Published,
            'published_at' => now(),
        ]);
    }
}
