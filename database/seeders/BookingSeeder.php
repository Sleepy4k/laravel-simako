<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\MessageThread;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $fixedUser = User::where('email', 'user@simako.com')->first();
        $rooms = Room::with('prices')->inRandomOrder()->limit(5)->get();

        if ($rooms->isEmpty()) {
            return;
        }

        $bookingData = [
            // Booking aktif
            [
                'status' => 'active',
                'start_date' => now()->subMonths(2)->toDateString(),
                'approved_at' => now()->subMonths(2),
            ],
            // Booking pending
            [
                'status' => 'pending',
                'start_date' => now()->addDays(7)->toDateString(),
            ],
            // Booking selesai
            [
                'status' => 'ended',
                'start_date' => now()->subMonths(8)->toDateString(),
                'end_date' => now()->subMonths(2)->toDateString(),
                'approved_at' => now()->subMonths(8),
            ],
        ];

        foreach ($bookingData as $i => $data) {
            $room = $rooms->get($i % $rooms->count());
            if (! $room) {
                continue;
            }

            $price = $room->prices->where('period', 'monthly')->first();
            if (! $price) {
                continue;
            }

            $booking = Booking::firstOrCreate(
                ['user_id' => $fixedUser->id, 'room_id' => $room->id, 'room_price_id' => $price->id],
                array_merge([
                    'user_id' => $fixedUser->id,
                    'room_id' => $room->id,
                    'room_price_id' => $price->id,
                    'notes' => 'Saya mahasiswa semester 4, butuh kost dekat kampus.',
                ], $data),
            );

            if (in_array($booking->status, ['active', 'approved', 'ended'], true)) {
                MessageThread::firstOrCreate(['booking_id' => $booking->id]);

                if (in_array($booking->status, ['active', 'ended'], true) && ! $room->bookings()->where('id', '!=', $booking->id)->exists()) {
                    $room->update(['is_available' => false]);
                }
            }
        }
    }
}
