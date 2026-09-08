<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Bill $bill): bool
    {
        return $user->hasAnyPermission(['generate bills', 'finalize bills', 'record payments', 'view reports']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('generate bills');
    }

    public function generate(User $user): bool
    {
        return $user->hasPermissionTo('generate bills');
    }

    public function finalize(User $user, Bill $bill): bool
    {
        return $user->hasPermissionTo('finalize bills');
    }

    public function update(User $user, Bill $bill): bool
    {
        if ($bill->isImmutable()) {
            return false;
        }

        return $user->hasPermissionTo('generate bills');
    }

    public function delete(User $user, Bill $bill): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermissionTo('generate bills') && ! $bill->isImmutable();
    }

    public function recordPayment(User $user, Bill $bill): bool
    {
        return $user->hasPermissionTo('record payments');
    }
}
