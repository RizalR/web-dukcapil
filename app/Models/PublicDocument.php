<?php

namespace App\Models;

use Database\Factories\PublicDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'slug', 'category', 'year', 'description', 'url', 'published_at', 'updated_by'])]
class PublicDocument extends Model
{
    /** @use HasFactory<PublicDocumentFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
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
