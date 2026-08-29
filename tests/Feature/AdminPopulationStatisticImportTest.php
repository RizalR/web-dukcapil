<?php

namespace Tests\Feature;

use App\AdministrativeAreaLevel;
use App\Http\Controllers\Admin\PopulationStatisticImportController;
use App\Models\PopulationStatistic;
use App\Models\User;
use App\PopulationStatisticCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminPopulationStatisticImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_population_statistic_import_form(): void
    {
        $this->get(route('admin.population-statistic-imports.create'))
            ->assertRedirect(route('login'));
    }

    public function test_news_editor_cannot_access_population_statistic_import(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.population-statistic-imports.create'))
            ->assertForbidden();
        $this->actingAs($editor)
            ->post(route('admin.population-statistic-imports.preview'))
            ->assertForbidden();
    }

    public function test_super_admin_can_open_form_for_all_aggregate_categories(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get(route('admin.population-statistic-imports.create'));

        $response
            ->assertOk()
            ->assertSee('Pilih satu atau beberapa workbook agregat')
            ->assertSee('name="workbooks[jenis-kelamin]"', false)
            ->assertSee('name="workbooks[ktp-el]"', false);

        foreach (PopulationStatisticCategory::cases() as $category) {
            $response->assertSee($category->value);
        }
    }

    public function test_preview_requires_at_least_one_workbook(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.population-statistic-imports.create'))
            ->post(route('admin.population-statistic-imports.preview'), $this->metadata())
            ->assertRedirect(route('admin.population-statistic-imports.create'))
            ->assertSessionHasErrors(['workbooks' => 'Pilih sedikitnya satu file Excel agregat.']);
    }

    public function test_super_admin_can_preview_one_selected_population_workbook(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('admin.population-statistic-imports.preview'), [
            ...$this->metadata(),
            'workbooks' => [
                PopulationStatisticCategory::Gender->slug() => $this->fixtureWorkbook(),
            ],
        ]);

        $response
            ->assertOk()
            ->assertSee('Periksa data sebelum disimpan')
            ->assertSee('Jenis Kelamin')
            ->assertViewHas('pendingImport', function (array $pendingImport): bool {
                $dataset = $pendingImport['datasets'][0] ?? null;

                return $pendingImport['dataset_count'] === 1
                    && $pendingImport['area_count'] === 5
                    && $pendingImport['statistic_count'] === 15
                    && is_array($dataset)
                    && $dataset['category'] === PopulationStatisticCategory::Gender->value
                    && $dataset['comparison'] === [
                        'new' => 15,
                        'updated' => 0,
                        'unchanged' => 0,
                        'retained' => 0,
                    ];
            });

        $token = $response->viewData('token');

        $this->assertIsString($token);
        $this->assertSame(40, strlen($token));
        $this->assertTrue(Cache::has(PopulationStatisticImportController::cacheKey($admin->id, $token)));
    }

    public function test_invalid_selected_workbook_rejects_the_whole_preview(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.population-statistic-imports.create'))
            ->post(route('admin.population-statistic-imports.preview'), [
                ...$this->metadata(),
                'workbooks' => [
                    PopulationStatisticCategory::Gender->slug() => $this->fixtureWorkbook(),
                    PopulationStatisticCategory::Religion->slug() => UploadedFile::fake()->createWithContent('agama.xlsx', 'bukan file xlsx'),
                ],
            ]);

        $response
            ->assertRedirect(route('admin.population-statistic-imports.create'))
            ->assertSessionHasErrors([
                'workbooks.agama' => 'File bukan arsip XLSX yang valid.',
            ]);
        $this->assertDatabaseCount('population_statistics', 0);
    }

    public function test_preview_reports_new_changed_unchanged_and_retained_values(): void
    {
        $admin = User::factory()->superAdmin()->create();
        PopulationStatistic::factory()->for($admin, 'updatedBy')->create([
            'label' => PopulationStatistic::LABEL_MALE,
            'category' => PopulationStatisticCategory::Gender->value,
            'value' => 999,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);
        PopulationStatistic::factory()->for($admin, 'updatedBy')->create([
            'label' => PopulationStatistic::LABEL_FEMALE,
            'category' => PopulationStatisticCategory::Gender->value,
            'value' => 110,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);
        PopulationStatistic::factory()->for($admin, 'updatedBy')->create([
            'label' => 'Indikator lama',
            'category' => PopulationStatisticCategory::Gender->value,
            'value' => 1,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.population-statistic-imports.preview'), [
            ...$this->metadata(),
            'workbooks' => [
                PopulationStatisticCategory::Gender->slug() => $this->fixtureWorkbook(),
            ],
        ]);

        $response
            ->assertOk()
            ->assertSee('1 data lama tidak ditemukan dalam file ini')
            ->assertViewHas('pendingImport', fn (array $pendingImport): bool => $pendingImport['datasets'][0]['comparison'] === [
                'new' => 13,
                'updated' => 1,
                'unchanged' => 1,
                'retained' => 1,
            ]);
    }

    public function test_super_admin_can_import_previewed_statistics_as_draft(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $token = str_repeat('a', 40);
        Cache::put(
            PopulationStatisticImportController::cacheKey($admin->id, $token),
            $this->pendingImport(),
            now()->addMinutes(30),
        );

        $response = $this->actingAs($admin)->post(route('admin.population-statistic-imports.store'), [
            'token' => $token,
        ]);

        $response
            ->assertRedirect(route('admin.population-statistics.index'))
            ->assertSessionHas('status', '15 statistik dari 1 kategori berhasil diimpor sebagai draf.');
        $this->assertDatabaseCount('population_statistics', 15);
        $this->assertDatabaseHas('population_statistics', [
            'label' => PopulationStatistic::LABEL_TOTAL,
            'period' => 'Semester I 2026',
            'area_code' => '75.01',
            'value' => 210,
            'published_at' => null,
            'updated_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'statistic.imported',
            'user_id' => $admin->id,
        ]);
        $this->assertFalse(Cache::has(PopulationStatisticImportController::cacheKey($admin->id, $token)));
    }

    public function test_import_updates_selected_category_without_touching_an_unselected_category(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $existingTotal = PopulationStatistic::factory()->for($admin, 'updatedBy')->create([
            'label' => PopulationStatistic::LABEL_TOTAL,
            'category' => PopulationStatisticCategory::Gender->value,
            'value' => 999,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);
        $religion = PopulationStatistic::factory()->for($admin, 'updatedBy')->create([
            'label' => 'Islam',
            'category' => PopulationStatisticCategory::Religion->value,
            'value' => 99,
            'period' => 'Semester I 2026',
            'area' => 'GORONTALO',
            'area_code' => '75.01',
            'area_level' => AdministrativeAreaLevel::Regency,
        ]);
        $token = str_repeat('d', 40);
        Cache::put(
            PopulationStatisticImportController::cacheKey($admin->id, $token),
            $this->pendingImport(),
            now()->addMinutes(30),
        );

        $this->actingAs($admin)->post(route('admin.population-statistic-imports.store'), [
            'token' => $token,
        ])->assertRedirect(route('admin.population-statistics.index'));

        $this->assertDatabaseHas('population_statistics', [
            'id' => $existingTotal->id,
            'category' => PopulationStatisticCategory::Gender->value,
            'value' => 210,
        ]);
        $this->assertDatabaseHas('population_statistics', [
            'id' => $religion->id,
            'category' => PopulationStatisticCategory::Religion->value,
            'value' => 99,
        ]);
        $this->assertDatabaseCount('population_statistics', 16);
    }

    public function test_super_admin_can_preview_and_publish_all_eight_workbooks(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('admin.population-statistic-imports.preview'), [
            ...$this->metadata(),
            'workbooks' => $this->aggregateWorkbooks(),
        ]);

        $response
            ->assertOk()
            ->assertViewHas('pendingImport', fn (array $pendingImport): bool => $pendingImport['dataset_count'] === 8
                && $pendingImport['area_count'] === 225
                && $pendingImport['statistic_count'] === 1695);

        $token = $response->viewData('token');

        $this->actingAs($admin)->post(route('admin.population-statistic-imports.store'), [
            'token' => $token,
            'publish_now' => '1',
        ])
            ->assertRedirect(route('admin.population-statistics.index'))
            ->assertSessionHas('status', '1695 statistik dari 8 kategori berhasil diimpor dan diterbitkan.');

        $this->assertDatabaseCount('population_statistics', 1695);
        $this->assertDatabaseHas('population_statistics', [
            'category' => PopulationStatisticCategory::HouseholdHead->value,
            'label' => 'Jumlah Kepala Keluarga',
            'area_code' => '75.01',
            'value' => 143302,
            'updated_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('population_statistics', [
            'category' => PopulationStatisticCategory::Religion->value,
            'label' => 'Islam',
            'area_code' => '75.01',
            'value' => 427790,
        ]);
    }

    public function test_super_admin_can_publish_statistics_during_confirmed_import(): void
    {
        $this->freezeTime(function (): void {
            $admin = User::factory()->superAdmin()->create();
            $token = str_repeat('b', 40);
            Cache::put(
                PopulationStatisticImportController::cacheKey($admin->id, $token),
                $this->pendingImport(),
                now()->addMinutes(30),
            );

            $this->actingAs($admin)->post(route('admin.population-statistic-imports.store'), [
                'token' => $token,
                'publish_now' => '1',
            ])->assertRedirect(route('admin.population-statistics.index'));

            $statistic = PopulationStatistic::query()
                ->where('label', PopulationStatistic::LABEL_TOTAL)
                ->where('area_code', '75.01')
                ->firstOrFail();

            $this->assertNotNull($statistic->published_at);
            $this->assertSame(now()->toDateTimeString(), $statistic->published_at->toDateTimeString());
        });
    }

    public function test_expired_preview_cannot_be_imported(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('admin.population-statistic-imports.store'), [
            'token' => str_repeat('c', 40),
        ]);

        $response
            ->assertRedirect(route('admin.population-statistic-imports.create'))
            ->assertSessionHasErrors(['workbooks' => 'Pratinjau impor sudah kedaluwarsa. Silakan unggah ulang file Excel.']);
        $this->assertDatabaseCount('population_statistics', 0);
    }

    /** @return array{period: string, source: string, source_url: string} */
    private function metadata(): array
    {
        return [
            'period' => 'Semester I 2026',
            'source' => 'Dinas Dukcapil Kabupaten Gorontalo',
            'source_url' => 'https://dukcapil.gorontalokab.go.id/',
        ];
    }

    private function fixtureWorkbook(): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/Fixtures/population-statistics.xlsx'),
            'aggregate.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    /** @return array<string, UploadedFile> */
    private function aggregateWorkbooks(): array
    {
        $files = [
            PopulationStatisticCategory::Gender->slug() => 'AGR_JK_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::HouseholdHead->slug() => 'AGR_KEPALA_KELUARGA_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::AgeGroup->slug() => 'AGR_JK_KEL_UMUR_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::Religion->slug() => 'AGR_AGAMA_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::Education->slug() => 'AGR_PDDKN_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::MaritalStatus->slug() => 'AGR_STAT_KWN_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::Disability->slug() => 'AGR_DISABILITAS_VERT_202601_7501_GORONTALO.xlsx',
            PopulationStatisticCategory::ElectronicIdentityCard->slug() => 'AGR_WKTP_REKAM_CETAK_202601_7501_GORONTALO.xlsx',
        ];

        return collect($files)->mapWithKeys(fn (string $filename, string $slug): array => [
            $slug => new UploadedFile(
                database_path("AGR 2026 SEM 1/{$filename}"),
                $filename,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true,
            ),
        ])->all();
    }

    /**
     * @return array{
     *     period: string,
     *     source: string,
     *     source_url: string,
     *     dataset_count: int,
     *     area_count: int,
     *     statistic_count: int,
     *     datasets: list<array{category: string, filename: string, area_count: int, statistic_count: int, comparison: array{new: int, updated: int, unchanged: int, retained: int}, rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>}>
     * }
     */
    private function pendingImport(): array
    {
        $rows = [
            ['area_code' => '75.01', 'area' => 'GORONTALO', 'area_level' => AdministrativeAreaLevel::Regency->value, 'parent_area_code' => null, 'values' => [PopulationStatistic::LABEL_MALE => 100, PopulationStatistic::LABEL_FEMALE => 110, PopulationStatistic::LABEL_TOTAL => 210]],
            ['area_code' => '75.01.01', 'area' => 'KECAMATAN ALFA', 'area_level' => AdministrativeAreaLevel::District->value, 'parent_area_code' => '75.01', 'values' => [PopulationStatistic::LABEL_MALE => 60, PopulationStatistic::LABEL_FEMALE => 70, PopulationStatistic::LABEL_TOTAL => 130]],
            ['area_code' => '75.01.01.1001', 'area' => 'DESA ALFA', 'area_level' => AdministrativeAreaLevel::Village->value, 'parent_area_code' => '75.01.01', 'values' => [PopulationStatistic::LABEL_MALE => 60, PopulationStatistic::LABEL_FEMALE => 70, PopulationStatistic::LABEL_TOTAL => 130]],
            ['area_code' => '75.01.02', 'area' => 'KECAMATAN BETA', 'area_level' => AdministrativeAreaLevel::District->value, 'parent_area_code' => '75.01', 'values' => [PopulationStatistic::LABEL_MALE => 40, PopulationStatistic::LABEL_FEMALE => 40, PopulationStatistic::LABEL_TOTAL => 80]],
            ['area_code' => '75.01.02.2001', 'area' => 'DESA BETA', 'area_level' => AdministrativeAreaLevel::Village->value, 'parent_area_code' => '75.01.02', 'values' => [PopulationStatistic::LABEL_MALE => 40, PopulationStatistic::LABEL_FEMALE => 40, PopulationStatistic::LABEL_TOTAL => 80]],
        ];

        return [
            ...$this->metadata(),
            'dataset_count' => 1,
            'area_count' => 5,
            'statistic_count' => 15,
            'datasets' => [[
                'category' => PopulationStatisticCategory::Gender->value,
                'filename' => 'aggregate.xlsx',
                'area_count' => 5,
                'statistic_count' => 15,
                'comparison' => ['new' => 15, 'updated' => 0, 'unchanged' => 0, 'retained' => 0],
                'rows' => $rows,
            ]],
        ];
    }
}
