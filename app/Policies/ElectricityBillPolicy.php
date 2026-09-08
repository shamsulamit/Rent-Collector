<?php

namespace App\Policies;

use App\Models\ElectricityBill;
use App\Models\User;

class ElectricityBillPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ElectricityBill $bill): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage readings');
    }

    public function update(User $user, ElectricityBill $bill): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermissionTo('manage readings') && ! $bill->isImmutable();
    }

    public function delete(User $user, ElectricityBill $bill): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermissionTo('manage readings') && ! $bill->isImmutable();
    }

    public function finalize(User $user, ElectricityBill $bill): bool
    {
        return $user->hasAnyPermission(['manage readings', 'finalize bills']) || $user->isOwner();
    }
}
