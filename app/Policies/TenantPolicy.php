<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage tenants');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->hasPermissionTo('manage tenants');
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->hasRole('owner');
    }
}
