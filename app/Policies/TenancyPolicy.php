<?php

namespace App\Policies;

use App\Models\Tenancy;
use App\Models\User;

class TenancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage tenancies');
    }

    public function view(User $user, Tenancy $tenancy): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage tenancies');
    }

    public function update(User $user, Tenancy $tenancy): bool
    {
        return $user->hasPermissionTo('manage tenancies');
    }

    public function delete(User $user, Tenancy $tenancy): bool
    {
        return $user->isOwner();
    }
}
