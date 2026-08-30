<?php

namespace App\Policies;

use App\Models\MaintenanceTicket;
use App\Models\User;

class MaintenanceTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MaintenanceTicket $ticket): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage maintenance');
    }

    public function update(User $user, MaintenanceTicket $ticket): bool
    {
        return $user->hasPermissionTo('manage maintenance');
    }

    public function delete(User $user, MaintenanceTicket $ticket): bool
    {
        return $user->hasRole('owner');
    }
}
