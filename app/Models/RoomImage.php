<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $room_id
 * @property string $path
 * @property string|null $caption
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['room_id', 'path', 'caption', 'sort_order'])]
class RoomImage extends Model
{
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
