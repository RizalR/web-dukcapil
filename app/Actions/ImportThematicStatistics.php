<?php

namespace App\Actions;

use App\Models\ThematicStatisticDataset;
use App\Models\ThematicStatisticObservation;
use App\Models\User;
use App\ThematicStatisticDatasetStatus;
use Illuminate\Support\Facades\DB;

class ImportThematicStatistics
{
    /**
     * @param array{
     *     period: string,
     *     source: string,
     *     source_url?: string|null,
     *     datasets: list<array{type: string, filename: string, file_hash: string, row_count: int, observation_count: int, observations: list<array<string, string|null>>}>
     * } $pendingImport
     * @return list<ThematicStatisticDataset>
     */
    public function handle(array $pendingImport, User $user, bool $publishNow): array
    {
        return DB::transaction(function () use ($pendingImport, $user, $publishNow): array {
            $datasets = [];
            $timestamp = now();

            foreach ($pendingImport['datasets'] as $datasetData) {
                if ($publishNow) {
                    ThematicStatisticDataset::query()
                        ->where('type', $datasetData['type'])
                        ->where('period', $pendingImport['period'])
                        ->where('status', ThematicStatisticDatasetStatus::Published->value)
                        ->lockForUpdate()
                        ->update([
                            'status' => ThematicStatisticDatasetStatus::Superseded->value,
                            'updated_at' => $timestamp,
                        ]);
                }

                $dataset = ThematicStatisticDataset::query()->create([
                    'type' => $datasetData['type'],
                    'period' => $pendingImport['period'],
                    'source' => $pendingImport['source'],
                    'source_url' => $pendingImport['source_url'] ?? null,
                    'original_filename' => $datasetData['filename'],
                    'file_hash' => $datasetData['file_hash'],
                    'status' => $publishNow
                        ? ThematicStatisticDatasetStatus::Published
                        : ThematicStatisticDatasetStatus::Draft,
                    'row_count' => $datasetData['row_count'],
                    'observation_count' => $datasetData['observation_count'],
                    'published_at' => $publishNow ? $timestamp : null,
                    'imported_by' => $user->id,
                ]);

                foreach (array_chunk($datasetData['observations'], 500) as $observationChunk) {
                    ThematicStatisticObservation::query()->insert(array_map(
                        fn (array $observation): array => [
                            ...$observation,
                            'dataset_id' => $dataset->id,
                        ],
                        $observationChunk,
                    ));
                }

                $datasets[] = $dataset;
            }

            return $datasets;
        });
    }
}
