<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class SimakoUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials (email OR phone).
     *
     * @param  array<string, mixed>  $credentials
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $login = $credentials['login'] ?? null;

        if (! $login) {
            return null;
        }

        $field = str_contains($login, '@') ? 'email' : 'phone';

        /** @var User|null */
        return $this->createModel()
            ->newQuery()
            ->where($field, $login)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $this->hasher->check(
            $credentials['password'] ?? '',
            $user->getAuthPassword(),
        );
    }
}
