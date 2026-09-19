<?php

namespace App\Policies;

use App\Models\InternalEntity;
use App\Models\User;

class InternalEntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('internal-entities.view') || $user->hasPermission('internal-entities.index');
    }

    public function view(User $user, ?InternalEntity $model = null): bool
    {
        return $user->hasPermission('internal-entities.view') || $user->hasPermission('internal-entities.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('internal-entities.create');
    }

    public function update(User $user, ?InternalEntity $model = null): bool
    {
        return $user->hasPermission('internal-entities.update') || $user->hasPermission('internal-entities.edit');
    }

    public function delete(User $user, ?InternalEntity $model = null): bool
    {
        return $user->hasPermission('internal-entities.delete') || $user->hasPermission('internal-entities.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('internal-entities.import') || $user->hasPermission('internal-entities.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('internal-entities.export') || $user->hasPermission('internal-entities.view');
    }
}
