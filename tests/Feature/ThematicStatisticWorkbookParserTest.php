<?php

namespace Tests\Feature;

use App\Services\ThematicStatisticWorkbookParser;
use App\ThematicStatisticType;
use Tests\TestCase;

class ThematicStatisticWorkbookParserTest extends TestCase
{
    public function test_it_parses_the_official_apak_workbook_and_recalculates_its_percentage(): void
    {
        $filename = 'AGR_APAK_202601_7501_GORONTALO.xlsx';
        $result = app(ThematicStatisticWorkbookParser::class)->parse(
            database_path("AGR 2026 SEM 1/{$filename}"),
            ThematicStatisticType::LaborForceParticipation,
            $filename,
        );
        $regencyPercentage = collect($result['observations'])->first(
            fn (array $observation): bool => $observation['area_code'] === '75.01'
                && $observation['metric'] === 'participation_rate'
                && $observation['sex'] === 'total',
        );

        $this->assertSame('49.5610', $regencyPercentage['value']);
        $this->assertGreaterThan(1, $result['area_count']);
        $this->assertSame($result['row_count'] * 18, $result['observation_count']);
    }

    public function test_it_streams_the_large_single_age_education_workbook(): void
    {
        $filename = 'AGR_PENDIDIKAN_UMUR_TUNGGAL_202601_7501_GORONTALO.xlsx';
        $result = app(ThematicStatisticWorkbookParser::class)->parse(
            database_path("AGR 2026 SEM 1/{$filename}"),
            ThematicStatisticType::EducationBySingleAge,
            $filename,
        );

        $this->assertSame(103, $result['row_count']);
        $this->assertSame(1, $result['area_count']);
        $this->assertSame(3_090, $result['observation_count']);
        $this->assertContains('0', array_column($result['observations'], 'value'));
    }
}
