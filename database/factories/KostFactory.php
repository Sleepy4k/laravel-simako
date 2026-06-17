<?php

namespace Database\Factories;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kost>
 */
class KostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Kost '.$this->faker->lastName();

        return [
            'user_id' => User::factory()->tenant(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['putra', 'putri', 'campur']),
            'status' => 'active',
            'total_rooms' => 0,
            'available_rooms' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }

    public function withAddress(): static
    {
        return $this->afterCreating(function (Kost $kost) {
            $kost->address()->create([
                'city' => $this->faker->city(),
                'province' => 'Jawa Barat',
                'street' => $this->faker->streetAddress(),
                'district' => $this->faker->city(),
            ]);
        });
    }
}
