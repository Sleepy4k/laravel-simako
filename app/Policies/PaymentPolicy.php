<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isPengguna()) {
            return $payment->booking->user_id === $user->id;
        }

        if ($user->isTenant()) {
            return $payment->booking->room->kost->user_id === $user->id;
        }

        return $user->isAdmin();
    }

    public function uploadProof(User $user, Payment $payment): bool
    {
        return $user->isPengguna()
            && $payment->booking->user_id === $user->id
            && in_array($payment->status, ['unpaid', 'declined'], true);
    }

    public function approve(User $user, Payment $payment): bool
    {
        return $user->isTenant() && $payment->booking->room->kost->user_id === $user->id;
    }
}
