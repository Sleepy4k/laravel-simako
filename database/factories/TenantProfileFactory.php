<?php

namespace Database\Factories;

use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantProfile>
 */
class TenantProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tenant(),
            'business_name' => 'Kost '.$this->faker->lastName(),
            'identity_image' => null,
            'verified_at' => null,
            'verified_by' => null,
            'suspended_at' => null,
            'suspension_reason' => null,
        ];
    }

    public function verified(int $verifiedById): static
    {
        return $this->state([
            'verified_at' => now(),
            'verified_by' => $verifiedById,
        ]);
    }
}
