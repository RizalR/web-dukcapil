<?php

namespace App\Models;

use Database\Factories\ThematicStatisticObservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dataset_id', 'area_code', 'area', 'area_level', 'parent_area_code', 'dimension_one', 'dimension_one_value', 'dimension_two', 'dimension_two_value', 'metric', 'sex', 'snapshot', 'value', 'unit', 'observation_key'])]
class ThematicStatisticObservation extends Model
{
    /** @use HasFactory<ThematicStatisticObservationFactory> */
    use HasFactory;

    public $timestamps = false;

    /** @return BelongsTo<ThematicStatisticDataset, $this> */
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(ThematicStatisticDataset::class, 'dataset_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
        ];
    }
}
