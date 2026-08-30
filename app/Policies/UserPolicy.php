<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasRole('owner') && $target->id !== $user->id;
    }
}
