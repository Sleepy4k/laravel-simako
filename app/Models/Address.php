<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $addressable_type
 * @property int $addressable_id
 * @property string|null $street
 * @property string|null $district
 * @property string $city
 * @property string $province
 * @property string|null $postal_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['street', 'district', 'city', 'province', 'postal_code', 'latitude', 'longitude'])]
class Address extends Model
{
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
