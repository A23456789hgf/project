<?php

namespace App\Policies;

use App\Models\EntityAuthority;
use App\Models\User;

class EntityAuthorityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('entity-authorities.view') || $user->hasPermission('entity-authorities.index');
    }

    public function view(User $user, ?EntityAuthority $model = null): bool
    {
        return $user->hasPermission('entity-authorities.view') || $user->hasPermission('entity-authorities.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('entity-authorities.create');
    }

    public function update(User $user, ?EntityAuthority $model = null): bool
    {
        return $user->hasPermission('entity-authorities.update') || $user->hasPermission('entity-authorities.edit');
    }

    public function delete(User $user, ?EntityAuthority $model = null): bool
    {
        return $user->hasPermission('entity-authorities.delete') || $user->hasPermission('entity-authorities.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('entity-authorities.import') || $user->hasPermission('entity-authorities.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('entity-authorities.export') || $user->hasPermission('entity-authorities.view');
    }
}
