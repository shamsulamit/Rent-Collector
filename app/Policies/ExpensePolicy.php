<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage expenses', 'view reports']);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage expenses');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->hasPermissionTo('manage expenses');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->hasRole('owner');
    }
}
