<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->active(),
            'period_start' => now()->toDateString(),
            'period_end' => now()->addMonth()->subDay()->toDateString(),
            'amount' => 800000,
            'status' => 'unpaid',
            'due_date' => now()->addDays(3)->toDateString(),
        ];
    }

    public function unpaid(): static
    {
        return $this->state(['status' => 'unpaid']);
    }

    public function pendingVerification(): static
    {
        return $this->state(['status' => 'pending_verification']);
    }

    public function paid(): static
    {
        return $this->state([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state([
            'status' => 'declined',
            'declined_at' => now(),
            'decline_notes' => 'Bukti tidak valid',
        ]);
    }
}
