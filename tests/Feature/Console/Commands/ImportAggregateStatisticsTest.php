<?php

namespace Tests\Feature\Console\Commands;

use App\AdministrativeAreaLevel;
use App\Models\PopulationStatistic;
use App\PopulationStatisticCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportAggregateStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_imports_and_publishes_eight_validated_aggregate_datasets(): void
    {
        $this->artisan('statistics:import-aggregate-directory', ['--publish' => true])
            ->expectsOutputToContain('8 dataset, 225 wilayah unik, dan 1695 nilai berhasil diimpor sebagai data terbit.')
            ->assertSuccessful();

        $this->assertDatabaseCount('population_statistics', 1695);
        $this->assertDatabaseHas('population_statistics', [
            'category' => PopulationStatisticCategory::Gender->value,
            'label' => PopulationStatistic::LABEL_TOTAL,
            'area_code' => '75.01',
            'value' => 430205,
            'period' => 'Semester I 2026',
        ]);
        $this->assertDatabaseHas('population_statistics', [
            'category' => PopulationStatisticCategory::HouseholdHead->value,
            'label' => 'Jumlah Kepala Keluarga',
            'area_code' => '75.01',
            'value' => 143302,
            'area_level' => AdministrativeAreaLevel::Regency->value,
        ]);
        $this->assertDatabaseHas('population_statistics', [
            'category' => PopulationStatisticCategory::Religion->value,
            'label' => 'Islam',
            'area_code' => '75.01',
            'value' => 427790,
        ]);
        $this->assertDatabaseMissing('population_statistics', [
            'category' => PopulationStatisticCategory::Religion->value,
            'area_level' => AdministrativeAreaLevel::Village->value,
        ]);
    }

    public function test_command_rejects_a_source_directory_outside_database(): void
    {
        $this->artisan('statistics:import-aggregate-directory', ['directory' => base_path('app')])
            ->expectsOutputToContain('Direktori sumber harus berupa folder yang dapat dibaca di bawah folder database.')
            ->assertFailed();

        $this->assertDatabaseCount('population_statistics', 0);
    }
}
