<?php

namespace Tests\Feature;

use App\AdministrativeAreaLevel;
use App\Http\Controllers\Admin\ThematicStatisticImportController;
use App\Models\ThematicStatisticDataset;
use App\Models\ThematicStatisticObservation;
use App\Models\User;
use App\ThematicStatisticDatasetStatus;
use App\ThematicStatisticType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminThematicStatisticTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_open_the_thematic_statistic_import(): void
    {
        $editor = User::factory()->create();

        $this->get(route('admin.thematic-statistic-imports.create'))
            ->assertRedirect(route('login'));
        $this->actingAs($editor)
            ->get(route('admin.thematic-statistic-imports.create'))
            ->assertForbidden();
        $this->actingAs($editor)
            ->post(route('admin.thematic-statistic-imports.preview'))
            ->assertForbidden();
    }

    public function test_super_admin_can_preview_dynamic_document_data_without_hiding_small_values(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $workbook = <<<'HTML'
        <table>
            <tr><th>KODE</th><th>WILAYAH</th><th>WAJIB AKTA DINAMIS LK</th><th>WAJIB AKTA DINAMIS PR</th><th>WAJIB AKTA DINAMIS JML</th><th>MEMILIKI DINAMIS LK</th><th>MEMILIKI DINAMIS PR</th><th>MEMILIKI DINAMIS JML</th><th>BELUM MEMILIKI DINAMIS LK</th><th>BELUM MEMILIKI DINAMIS PR</th><th>BELUM MEMILIKI DINAMIS JML</th><th>PERSEN DINAMIS</th></tr>
            <tr><td>75.01</td><td>GORONTALO</td><td>4500</td><td>4247</td><td>8747</td><td>4499</td><td>4245</td><td>8744</td><td>1</td><td>2</td><td>3</td><td>99.97</td></tr>
        </table>
        HTML;

        $response = $this->actingAs($admin)->post(route('admin.thematic-statistic-imports.preview'), [
            ...$this->metadata(),
            'workbooks' => [
                ThematicStatisticType::BirthCertificateAgeZeroToOne->slug() => UploadedFile::fake()->createWithContent('akta-kelahiran.xls', $workbook),
            ],
        ]);

        $response
            ->assertOk()
            ->assertSee('Periksa data tematik')
            ->assertViewHas('pendingImport', function (array $pendingImport): bool {
                $notOwned = collect($pendingImport['datasets'][0]['observations'] ?? [])
                    ->first(fn (array $observation): bool => $observation['metric'] === 'not_owned' && $observation['sex'] === 'total');

                return $pendingImport['dataset_count'] === 1
                    && $pendingImport['observation_count'] === 12
                    && $notOwned['value'] === '3';
            });
    }

    public function test_super_admin_can_import_and_publish_a_previewed_dataset(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $token = str_repeat('a', 40);
        Cache::put(
            ThematicStatisticImportController::cacheKey($admin->id, $token),
            [
                ...$this->metadata(),
                'dataset_count' => 1,
                'area_count' => 1,
                'row_count' => 1,
                'observation_count' => 1,
                'datasets' => [[
                    'type' => ThematicStatisticType::ChildIdentityCard->value,
                    'title' => ThematicStatisticType::ChildIdentityCard->title(),
                    'filename' => 'kia.xls',
                    'file_hash' => hash('sha256', 'kia-version-1'),
                    'row_count' => 1,
                    'area_count' => 1,
                    'observation_count' => 1,
                    'warnings' => [],
                    'observations' => [$this->observation('not_owned', '3', 'people')],
                ]],
            ],
            now()->addMinutes(30),
        );

        $this->actingAs($admin)->post(route('admin.thematic-statistic-imports.store'), [
            'token' => $token,
            'publish_now' => '1',
        ])
            ->assertRedirect(route('admin.thematic-statistic-datasets.index'))
            ->assertSessionHas('status', '1 dataset berhasil diimpor dan diterbitkan.');

        $this->assertDatabaseHas('thematic_statistic_datasets', [
            'type' => ThematicStatisticType::ChildIdentityCard->value,
            'status' => ThematicStatisticDatasetStatus::Published->value,
            'imported_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('thematic_statistic_observations', [
            'metric' => 'not_owned',
            'value' => 3,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'thematic-statistic.imported',
            'user_id' => $admin->id,
        ]);
        $this->assertFalse(Cache::has(ThematicStatisticImportController::cacheKey($admin->id, $token)));
    }

    public function test_publishing_a_new_version_supersedes_the_previous_version(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $oldDataset = ThematicStatisticDataset::factory()->published()->create([
            'type' => ThematicStatisticType::LaborForceParticipation,
        ]);
        $newDataset = ThematicStatisticDataset::factory()->create([
            'type' => ThematicStatisticType::LaborForceParticipation,
            'period' => $oldDataset->period,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.published-thematic-statistic-datasets.update', $newDataset))
            ->assertRedirect();

        $this->assertSame(ThematicStatisticDatasetStatus::Superseded, $oldDataset->refresh()->status);
        $this->assertSame(ThematicStatisticDatasetStatus::Published, $newDataset->refresh()->status);
        $this->assertNotNull($newDataset->published_at);
    }

    public function test_public_dashboard_displays_small_document_values_exactly(): void
    {
        $dataset = ThematicStatisticDataset::factory()->published()->create([
            'type' => ThematicStatisticType::BirthCertificateAgeZeroToOne,
            'observation_count' => 4,
        ]);

        foreach ([
            ['target', '8747', 'people'],
            ['owned', '8744', 'people'],
            ['not_owned', '3', 'people'],
            ['coverage', '99.9657', 'percent'],
        ] as [$metric, $value, $unit]) {
            ThematicStatisticObservation::query()->create([
                ...$this->observation($metric, $value, $unit),
                'dataset_id' => $dataset->id,
            ]);
        }

        $this->get(route('thematic-statistics'))
            ->assertOk()
            ->assertSee('Akta Kelahiran usia 0–1')
            ->assertSee('Belum memiliki')
            ->assertSee('>3<', false)
            ->assertSee('99,97%');
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

    /** @return array<string, string|null> */
    private function observation(string $metric, string $value, string $unit): array
    {
        return [
            'area_code' => '75.01',
            'area' => 'GORONTALO',
            'area_level' => AdministrativeAreaLevel::Regency->value,
            'parent_area_code' => null,
            'dimension_one' => '',
            'dimension_one_value' => '',
            'dimension_two' => '',
            'dimension_two_value' => '',
            'metric' => $metric,
            'sex' => 'total',
            'snapshot' => 'dynamic',
            'value' => $value,
            'unit' => $unit,
            'observation_key' => sha1("75.01|{$metric}|total|dynamic"),
        ];
    }
}
