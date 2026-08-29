<?php

namespace App\Models;

use App\AdministrativeAreaLevel;
use Database\Factories\PopulationStatisticFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['label', 'category', 'value', 'unit', 'period', 'area', 'area_code', 'area_level', 'parent_area_code', 'source', 'source_url', 'published_at', 'updated_by'])]
class PopulationStatistic extends Model
{
    /** @use HasFactory<PopulationStatisticFactory> */
    use HasFactory;

    public const string CATEGORY_GENDER = 'Jenis Kelamin';

    public const string LABEL_FEMALE = 'Jumlah Penduduk Perempuan';

    public const string LABEL_MALE = 'Jumlah Penduduk Laki-laki';

    public const string LABEL_TOTAL = 'Jumlah Penduduk';

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'area_level' => AdministrativeAreaLevel::class,
            'value' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
