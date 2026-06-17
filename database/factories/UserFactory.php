<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::where('name', 'pengguna')->first()?->id ?? 3,
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'admin')->first()?->id ?? 1,
        ]);
    }

    public function tenant(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'tenant')->first()?->id ?? 2,
        ]);
    }

    public function pengguna(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name', 'pengguna')->first()?->id ?? 3,
        ]);
    }
}
