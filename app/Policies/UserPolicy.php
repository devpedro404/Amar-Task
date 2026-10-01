<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function delete(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->id !== $user->id;
    }
}