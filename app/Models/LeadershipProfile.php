<?php

namespace App\Models;

use App\LeadershipRole;
use Database\Factories\LeadershipProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['role', 'name', 'photo_path', 'sort_order', 'is_published', 'updated_by'])]
class LeadershipProfile extends Model
{
    /** @use HasFactory<LeadershipProfileFactory> */
    use HasFactory;

    public function photoUrl(): string
    {
        if (str($this->photo_path)->startsWith('images/')) {
            return asset($this->photo_path);
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'role' => LeadershipRole::class,
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
