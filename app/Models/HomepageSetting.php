<?php

namespace App\Models;

use Database\Factories\HomepageSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['background_image_path', 'updated_by'])]
class HomepageSetting extends Model
{
    /** @use HasFactory<HomepageSettingFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
