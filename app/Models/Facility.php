<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $facility_category_id
 * @property string $name
 * @property string|null $icon
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['facility_category_id', 'name', 'icon'])]
class Facility extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'facility_category_id');
    }

    public function kosts(): BelongsToMany
    {
        return $this->belongsToMany(Kost::class, 'kost_facilities');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_facilities');
    }
}
