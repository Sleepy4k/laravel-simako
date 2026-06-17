<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kost_id
 * @property string $path
 * @property string|null $caption
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['kost_id', 'path', 'caption', 'sort_order'])]
class KostImage extends Model
{
    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }
}
