<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $activeBookings = Booking::where('status', 'active')
            ->with('roomPrice')
            ->get();

        foreach ($activeBookings as $booking) {
            $price = $booking->roomPrice;
            if (! $price) {
                continue;
            }

            $months = match ($price->period) {
                'quarterly' => 3,
                'semi_annual' => 6,
                'annual' => 12,
                default => 1,
            };

            $start = Carbon::parse($booking->start_date);

            // Bulan 1 - sudah dibayar
            Payment::firstOrCreate(
                ['booking_id' => $booking->id, 'period_start' => $start->toDateString()],
                [
                    'period_end' => $start->copy()->addMonths($months)->subDay()->toDateString(),
                    'amount' => $price->price,
                    'status' => 'paid',
                    'due_date' => $start->copy()->addDays(3)->toDateString(),
                    'paid_at' => $start->copy()->addDays(1),
                ],
            );

            // Bulan 2 - unpaid
            $start2 = $start->copy()->addMonths($months);
            Payment::firstOrCreate(
                ['booking_id' => $booking->id, 'period_start' => $start2->toDateString()],
                [
                    'period_end' => $start2->copy()->addMonths($months)->subDay()->toDateString(),
                    'amount' => $price->price,
                    'status' => 'unpaid',
                    'due_date' => $start2->copy()->addDays(3)->toDateString(),
                ],
            );
        }

        // Ended bookings - semua sudah dibayar
        $endedBookings = Booking::where('status', 'ended')
            ->with('roomPrice')
            ->get();

        foreach ($endedBookings as $booking) {
            $price = $booking->roomPrice;
            if (! $price) {
                continue;
            }

            $months = match ($price->period) {
                'quarterly' => 3,
                'semi_annual' => 6,
                'annual' => 12,
                default => 1,
            };

            $start = Carbon::parse($booking->start_date);
            $end = Carbon::parse($booking->end_date ?? now());

            while ($start->lt($end)) {
                $periodEnd = $start->copy()->addMonths($months)->subDay();

                Payment::firstOrCreate(
                    ['booking_id' => $booking->id, 'period_start' => $start->toDateString()],
                    [
                        'period_end' => $periodEnd->toDateString(),
                        'amount' => $price->price,
                        'status' => 'paid',
                        'due_date' => $start->copy()->addDays(3)->toDateString(),
                        'paid_at' => $start->copy()->addDays(2),
                    ],
                );

                $start->addMonths($months);
            }
        }
    }
}
