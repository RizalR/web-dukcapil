<?php

namespace App\Actions;

use App\Models\ThematicStatisticDataset;
use App\ThematicStatisticDatasetStatus;
use Illuminate\Support\Facades\DB;

class PublishThematicStatisticDataset
{
    public function handle(ThematicStatisticDataset $dataset): void
    {
        DB::transaction(function () use ($dataset): void {
            ThematicStatisticDataset::query()
                ->whereKeyNot($dataset->getKey())
                ->where('type', $dataset->type->value)
                ->where('period', $dataset->period)
                ->where('status', ThematicStatisticDatasetStatus::Published->value)
                ->lockForUpdate()
                ->update([
                    'status' => ThematicStatisticDatasetStatus::Superseded->value,
                    'updated_at' => now(),
                ]);

            $dataset->update([
                'status' => ThematicStatisticDatasetStatus::Published,
                'published_at' => now(),
            ]);
        });
    }
}
