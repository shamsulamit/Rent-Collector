<?php

namespace App\Policies;

use App\Models\Meter;
use App\Models\User;

class MeterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Meter $meter): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage meters');
    }

    public function update(User $user, Meter $meter): bool
    {
        return $user->hasPermissionTo('manage meters');
    }

    public function delete(User $user, Meter $meter): bool
    {
        return $user->hasRole('owner');
    }
}
