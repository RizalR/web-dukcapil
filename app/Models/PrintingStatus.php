<?php

namespace App\Models;

use App\PrintingDocument;
use Database\Factories\PrintingStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['location', 'document', 'sort_order', 'is_available', 'note', 'source', 'checked_at', 'updated_by'])]
class PrintingStatus extends Model
{
    /** @use HasFactory<PrintingStatusFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function availabilityLabel(): string
    {
        if ($this->checked_at === null) {
            return 'Belum diperbarui';
        }

        return $this->is_available ? 'Ada' : 'Tidak ada';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'document' => PrintingDocument::class,
            'is_available' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }
}
