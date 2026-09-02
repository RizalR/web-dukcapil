<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'source',
    'last_attempted_at',
    'last_succeeded_at',
    'last_snapshot_generated_at',
    'last_item_count',
    'last_error_at',
    'last_error_code',
])]
class WhistleblowingSyncState extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_attempted_at' => 'datetime',
            'last_succeeded_at' => 'datetime',
            'last_snapshot_generated_at' => 'datetime',
            'last_item_count' => 'integer',
            'last_error_at' => 'datetime',
        ];
    }
}
