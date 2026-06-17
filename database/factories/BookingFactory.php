<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->pengguna(),
            'room_id' => Room::factory(),
            'room_price_id' => RoomPrice::factory(),
            'status' => 'pending',
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => null,
            'notes' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function active(): static
    {
        return $this->state([
            'status' => 'active',
            'approved_at' => now()->subDays(5),
            'start_date' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Test cancellation',
        ]);
    }
}
