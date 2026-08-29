<?php

namespace App\Actions;

use App\Models\PopulationStatistic;
use App\Models\User;
use App\PopulationStatisticCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImportPopulationStatistics
{
    /**
     * @param array{
     *     period: string,
     *     source: string,
     *     source_url?: string|null,
     *     datasets: list<array{category: string, filename: string, area_count: int, statistic_count: int, rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>}>
     * } $pendingImport
     * @return array{period: string, dataset_count: int, area_count: int, statistic_count: int, published: bool, datasets: list<array{category: string, area_count: int, statistic_count: int}>}
     */
    public function handle(array $pendingImport, ?User $user, bool $publish): array
    {
        $timestamp = now();
        $publishedAt = $publish ? $timestamp : null;
        $statistics = [];
        $datasetResults = [];
        $uniqueAreas = [];

        foreach ($pendingImport['datasets'] as $dataset) {
            $category = PopulationStatisticCategory::tryFrom($dataset['category']);

            if (! $category instanceof PopulationStatisticCategory) {
                throw new InvalidArgumentException("Kategori statistik {$dataset['category']} tidak didukung.");
            }

            $source = Str::limit("{$pendingImport['source']} · {$dataset['filename']}", 180, '');

            foreach ($dataset['rows'] as $row) {
                $uniqueAreas[$row['area_code']] = true;

                foreach ($row['values'] as $label => $value) {
                    $statistics[] = [
                        'label' => $label,
                        'category' => $category->value,
                        'value' => $value,
                        'unit' => $category->unit(),
                        'period' => $pendingImport['period'],
                        'area' => $row['area'],
                        'area_code' => $row['area_code'],
                        'area_level' => $row['area_level'],
                        'parent_area_code' => $row['parent_area_code'],
                        'source' => $source,
                        'source_url' => $pendingImport['source_url'] ?? null,
                        'published_at' => $publishedAt,
                        'updated_by' => $user?->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }

            $datasetResults[] = [
                'category' => $category->value,
                'area_count' => $dataset['area_count'],
                'statistic_count' => $dataset['statistic_count'],
            ];
        }

        DB::transaction(function () use ($statistics): void {
            foreach (array_chunk($statistics, 200) as $chunk) {
                PopulationStatistic::query()->upsert(
                    $chunk,
                    ['label', 'category', 'period', 'area_code'],
                    [
                        'value',
                        'unit',
                        'area',
                        'area_level',
                        'parent_area_code',
                        'source',
                        'source_url',
                        'published_at',
                        'updated_by',
                        'updated_at',
                    ],
                );
            }
        });

        return [
            'period' => $pendingImport['period'],
            'dataset_count' => count($datasetResults),
            'area_count' => count($uniqueAreas),
            'statistic_count' => count($statistics),
            'published' => $publish,
            'datasets' => $datasetResults,
        ];
    }
}
