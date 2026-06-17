<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $business_name
 * @property string|null $identity_image
 * @property Carbon|null $verified_at
 * @property int|null $verified_by
 * @property Carbon|null $suspended_at
 * @property string|null $suspension_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $is_verified
 * @property-read bool $is_suspended
 */
#[Fillable(['user_id', 'business_name', 'identity_image', 'verified_at', 'verified_by', 'suspended_at', 'suspension_reason'])]
class TenantProfile extends Model
{
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verified_at !== null;
    }

    public function getIsSuspendedAttribute(): bool
    {
        return $this->suspended_at !== null;
    }
}
