<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Property $property): bool
    {
        return $user->hasAnyPermission(['manage properties', 'view reports']) || $user->hasRole(['owner', 'manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage properties');
    }

    public function update(User $user, Property $property): bool
    {
        return $user->hasPermissionTo('manage properties');
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->hasRole('owner');
    }
}
