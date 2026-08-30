<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->hasAnyPermission(['manage documents', 'manage tenants', 'manage properties']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage documents');
    }

    public function update(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('manage documents');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole('owner');
    }
}
