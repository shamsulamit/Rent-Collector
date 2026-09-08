<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['record payments', 'allocate payments', 'view reports']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('record payments');
    }

    public function allocate(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('allocate payments');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->isOwner();
    }
}
