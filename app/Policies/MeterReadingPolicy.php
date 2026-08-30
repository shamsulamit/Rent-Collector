<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;

class MeterReadingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MeterReading $reading): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage readings');
    }

    public function update(User $user, MeterReading $reading): bool
    {
        return $user->hasPermissionTo('manage readings');
    }

    public function delete(User $user, MeterReading $reading): bool
    {
        return $user->hasRole('owner');
    }
}
