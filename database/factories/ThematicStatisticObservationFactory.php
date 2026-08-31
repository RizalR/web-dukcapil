<?php

namespace Database\Factories;

use App\Models\ThematicStatisticDataset;
use App\Models\ThematicStatisticObservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ThematicStatisticObservation> */
class ThematicStatisticObservationFactory extends Factory
{
    protected $model = ThematicStatisticObservation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $identity = implode('|', ['75.01', '', '', 'participation_rate', 'total', 'dynamic']);

        return [
            'dataset_id' => ThematicStatisticDataset::factory(),
            'area_code' => '75.01',
            'area' => 'GORONTALO',
            'area_level' => 'kabupaten',
            'parent_area_code' => null,
            'dimension_one' => '',
            'dimension_one_value' => '',
            'dimension_two' => '',
            'dimension_two_value' => '',
            'metric' => 'participation_rate',
            'sex' => 'total',
            'snapshot' => 'dynamic',
            'value' => '49.5600',
            'unit' => 'percent',
            'observation_key' => sha1($identity),
        ];
    }
}
