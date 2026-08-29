<?php

namespace App\Models;

use App\SupplyLevel;
use Database\Factories\SupplyStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['location', 'item', 'status', 'note', 'source', 'checked_at', 'updated_by'])]
class SupplyStatus extends Model
{
    /** @use HasFactory<SupplyStatusFactory> */
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
            'status' => SupplyLevel::class,
            'checked_at' => 'datetime',
        ];
    }
}
