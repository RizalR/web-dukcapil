<?php

namespace App\Models;

use App\NewsStatus;
use Database\Factories\NewsArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['author_id', 'title', 'slug', 'excerpt', 'body', 'status', 'is_featured', 'source_url', 'published_at'])]
class NewsArticle extends Model
{
    /** @use HasFactory<NewsArticleFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => NewsStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query
            ->where('status', NewsStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
