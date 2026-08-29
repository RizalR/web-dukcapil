<?php

namespace App\Models;

use App\LinkCategory;
use Database\Factories\ExternalLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'description', 'url', 'is_active', 'sort_order', 'last_status_code', 'last_check_ok', 'last_checked_at'])]
class ExternalLink extends Model
{
    /** @use HasFactory<ExternalLinkFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'category' => LinkCategory::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'last_status_code' => 'integer',
            'last_check_ok' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }
}
