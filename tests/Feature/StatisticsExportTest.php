<?php

namespace Tests\Feature;

use App\AdministrativeAreaLevel;
use App\Models\PopulationStatistic;
use App\PopulationStatisticCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_contains_only_published_data_and_protects_against_formula_injection(): void
    {
        PopulationStatistic::factory()->create(['label' => '=DANGEROUS()', 'published_at' => now()->subMinute()]);
        PopulationStatistic::factory()->create(['label' => 'Belum Terbit', 'published_at' => null]);

        $response = $this->get(route('statistics.export'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Kode Wilayah', $content);
        $this->assertStringContainsString("'=DANGEROUS()", $content);
        $this->assertStringNotContainsString('Belum Terbit', $content);
    }

    public function test_filtered_export_includes_exact_small_and_district_values(): void
    {
        PopulationStatistic::factory()->create([
            'label' => 'Islam',
            'category' => PopulationStatisticCategory::Religion->value,
            'value' => 100,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);
        PopulationStatistic::factory()->create([
            'label' => 'Katolik kecil',
            'category' => PopulationStatisticCategory::Religion->value,
            'value' => 3,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);
        PopulationStatistic::factory()->create([
            'label' => 'Islam kecamatan',
            'category' => PopulationStatisticCategory::Religion->value,
            'value' => 50,
            'period' => 'Semester I 2026',
            'area' => 'KECAMATAN ALFA',
            'area_code' => '75.01.01',
            'area_level' => AdministrativeAreaLevel::District,
            'parent_area_code' => '75.01',
        ]);

        $response = $this->get(route('statistics.export', [
            'period' => 'Semester I 2026',
            'kategori' => PopulationStatisticCategory::Religion->slug(),
        ]));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Islam', $content);
        $this->assertStringContainsString('Katolik kecil', $content);
        $this->assertStringContainsString(',3,', $content);
        $this->assertStringContainsString('Islam kecamatan', $content);
        $this->assertStringContainsString('75.01.01', $content);
    }
}
