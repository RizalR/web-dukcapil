<?php

namespace App\Actions;

use App\Models\PopulationStatistic;

class PreparePopulationStatisticImport
{
    /**
     * @param  array{period: string, source: string, source_url?: string|null}  $metadata
     * @param  list<array{category: string, filename: string, area_count: int, statistic_count: int, rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>}>  $datasets
     * @return array{period: string, source: string, source_url?: string|null, dataset_count: int, area_count: int, statistic_count: int, datasets: list<array{category: string, filename: string, area_count: int, statistic_count: int, rows: list<array{area_code: string, area: string, area_level: string, parent_area_code: string|null, values: array<string, int>}>, comparison: array{new: int, updated: int, unchanged: int, retained: int}}>}
     */
    public function handle(array $metadata, array $datasets): array
    {
        $uniqueAreas = [];
        $statisticCount = 0;

        foreach ($datasets as &$dataset) {
            $existingStatistics = PopulationStatistic::query()
                ->where('period', $metadata['period'])
                ->where('category', $dataset['category'])
                ->get(['label', 'value', 'area', 'area_code', 'area_level', 'parent_area_code'])
                ->keyBy(fn (PopulationStatistic $statistic): string => $this->key($statistic->area_code, $statistic->label));
            $incomingKeys = [];
            $comparison = ['new' => 0, 'updated' => 0, 'unchanged' => 0, 'retained' => 0];

            foreach ($dataset['rows'] as $row) {
                $uniqueAreas[$row['area_code']] = true;

                foreach ($row['values'] as $label => $value) {
                    $key = $this->key($row['area_code'], $label);
                    $incomingKeys[$key] = true;
                    $existing = $existingStatistics->get($key);

                    if (! $existing instanceof PopulationStatistic) {
                        $comparison['new']++;

                        continue;
                    }

                    if ($existing->value !== $value
                        || $existing->area !== $row['area']
                        || $existing->area_level?->value !== $row['area_level']
                        || $existing->parent_area_code !== $row['parent_area_code']) {
                        $comparison['updated']++;

                        continue;
                    }

                    $comparison['unchanged']++;
                }
            }

            $comparison['retained'] = $existingStatistics
                ->keys()
                ->reject(fn (string $key): bool => isset($incomingKeys[$key]))
                ->count();
            $dataset['comparison'] = $comparison;
            $statisticCount += $dataset['statistic_count'];
        }
        unset($dataset);

        return [
            ...$metadata,
            'dataset_count' => count($datasets),
            'area_count' => count($uniqueAreas),
            'statistic_count' => $statisticCount,
            'datasets' => $datasets,
        ];
    }

    private function key(?string $areaCode, string $label): string
    {
        return $areaCode."\0".$label;
    }
}
