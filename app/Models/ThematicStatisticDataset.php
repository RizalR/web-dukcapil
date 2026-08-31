<?php

namespace App\Models;

use App\ThematicStatisticDatasetStatus;
use App\ThematicStatisticType;
use Database\Factories\ThematicStatisticDatasetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['type', 'period', 'source', 'source_url', 'original_filename', 'file_hash', 'status', 'row_count', 'observation_count', 'published_at', 'imported_by'])]
class ThematicStatisticDataset extends Model
{
    /** @use HasFactory<ThematicStatisticDatasetFactory> */
    use HasFactory;

    /** @return HasMany<ThematicStatisticObservation, $this> */
    public function observations(): HasMany
    {
        return $this->hasMany(ThematicStatisticObservation::class, 'dataset_id');
    }

    /** @return BelongsTo<User, $this> */
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ThematicStatisticType::class,
            'status' => ThematicStatisticDatasetStatus::class,
            'published_at' => 'datetime',
            'row_count' => 'integer',
            'observation_count' => 'integer',
        ];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query
            ->where('status', ThematicStatisticDatasetStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
