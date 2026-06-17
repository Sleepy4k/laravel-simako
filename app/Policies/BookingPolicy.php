<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isPengguna()) {
            return $booking->user_id === $user->id;
        }

        if ($user->isTenant()) {
            return $booking->room->kost->user_id === $user->id;
        }

        return $user->isAdmin();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->isPengguna()
            && $booking->user_id === $user->id
            && in_array($booking->status, ['pending', 'approved'], true);
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $user->isTenant() && $booking->room->kost->user_id === $user->id;
    }
}
