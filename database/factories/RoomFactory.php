<?php

namespace Database\Factories;

use App\Models\Kost;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kost_id' => Kost::factory(),
            'name' => 'Kamar '.$this->faker->randomLetter(),
            'floor' => $this->faker->numberBetween(1, 3),
            'size_sqm' => $this->faker->randomElement([9, 12, 14, 16]),
            'is_available' => true,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['is_available' => false]);
    }
}
