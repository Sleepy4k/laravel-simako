<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function update(User $user, Room $room): bool
    {
        return $user->isTenant() && $room->kost->user_id === $user->id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->isTenant() && $room->kost->user_id === $user->id;
    }
}
