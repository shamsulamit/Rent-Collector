<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage vendors', 'manage expenses', 'manage maintenance']);
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage vendors');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->hasPermissionTo('manage vendors');
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->isOwner() || $user->hasPermissionTo('manage vendors');
    }
}
