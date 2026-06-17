<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $endedBookings = Booking::where('status', 'ended')->doesntHave('review')->get();

        $reviews = [
            ['rating' => 5, 'comment' => 'Kost sangat nyaman dan bersih. Pemilik ramah dan responsif. Akan balik lagi!'],
            ['rating' => 4, 'comment' => 'Fasilitas cukup lengkap, harga sesuai. Lokasi strategis dekat kampus.'],
            ['rating' => 4, 'comment' => 'Secara keseluruhan memuaskan. Air panas lancar, wifi cepat.'],
        ];

        foreach ($endedBookings as $i => $booking) {
            $reviewData = $reviews[$i % count($reviews)];

            Review::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => $booking->user_id,
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment'],
                ],
            );
        }
    }
}
