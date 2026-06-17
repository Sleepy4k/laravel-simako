<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomPrice>
 */
class RoomPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'period' => 'monthly',
            'price' => $this->faker->randomElement([600000, 800000, 1000000, 1500000]),
            'deposit' => 800000,
        ];
    }
}
