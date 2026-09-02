<?php

namespace App\Models;

use Database\Factories\WhistleblowingPublicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'publication_id',
    'category_code',
    'category_label',
    'summary',
    'follow_up_result',
    'published_at',
    'source_updated_at',
    'last_synced_at',
    'is_active',
    'withdrawn_at',
])]
class WhistleblowingPublication extends Model
{
    /** @use HasFactory<WhistleblowingPublicationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'source_updated_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
            'withdrawn_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function visible(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
