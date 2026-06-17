<?php

namespace App\Policies;

use App\Models\Kost;
use App\Models\User;

class KostPolicy
{
    public function update(User $user, Kost $kost): bool
    {
        return $user->isTenant() && $kost->user_id === $user->id;
    }

    public function delete(User $user, Kost $kost): bool
    {
        return $user->isTenant() && $kost->user_id === $user->id;
    }
}
