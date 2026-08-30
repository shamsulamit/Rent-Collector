<?php

namespace App\Policies;

use App\Models\BackupRecord;
use App\Models\User;

class BackupRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage backups');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage backups');
    }

    public function restore(User $user, BackupRecord $record): bool
    {
        return $user->hasRole('owner') && $user->hasPermissionTo('restore backups');
    }

    public function delete(User $user, BackupRecord $record): bool
    {
        return $user->hasRole('owner');
    }
}
