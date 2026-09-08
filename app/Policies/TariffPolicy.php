<?php

namespace App\Policies;

use App\Models\Tariff;
use App\Models\User;

class TariffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage tariffs', 'manage readings', 'view reports']);
    }

    public function view(User $user, Tariff $tariff): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage tariffs');
    }

    public function update(User $user, Tariff $tariff): bool
    {
        return $user->hasPermissionTo('manage tariffs');
    }

    public function delete(User $user, Tariff $tariff): bool
    {
        return $user->isOwner() || $user->hasPermissionTo('manage tariffs');
    }
}
