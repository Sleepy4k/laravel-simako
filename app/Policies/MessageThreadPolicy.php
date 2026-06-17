<?php

namespace App\Policies;

use App\Models\MessageThread;
use App\Models\User;

class MessageThreadPolicy
{
    public function view(User $user, MessageThread $thread): bool
    {
        $booking = $thread->booking;

        return $booking->user_id === $user->id
            || $booking->room->kost->user_id === $user->id;
    }

    public function create(User $user, MessageThread $thread): bool
    {
        $booking = $thread->booking;

        return $booking->user_id === $user->id
            || $booking->room->kost->user_id === $user->id;
    }
}
