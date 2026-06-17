<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property string $status
 * @property string|null $thumbnail
 * @property int $total_rooms
 * @property int $available_rooms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'name', 'slug', 'description', 'type', 'status', 'thumbnail', 'total_rooms', 'available_rooms'])]
class Kost extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Kost $kost) {
            if (empty($kost->slug)) {
                $kost->slug = Str::slug($kost->name);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function images(): HasMany
    {
        return $this->hasMany(KostImage::class)->orderBy('sort_order');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'kost_facilities');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }
}
