<?php

namespace App\Policies;

use App\Models\TypeEntity;
use App\Models\User;

class TypeEntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('type-entity.view') || $user->hasPermission('type-entity.index');
    }

    public function view(User $user, ?TypeEntity $model = null): bool
    {
        return $user->hasPermission('type-entity.view') || $user->hasPermission('type-entity.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('type-entity.create');
    }

    public function update(User $user, ?TypeEntity $model = null): bool
    {
        return $user->hasPermission('type-entity.update') || $user->hasPermission('type-entity.edit');
    }

    public function delete(User $user, ?TypeEntity $model = null): bool
    {
        return $user->hasPermission('type-entity.delete') || $user->hasPermission('type-entity.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('type-entity.import') || $user->hasPermission('type-entity.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('type-entity.export') || $user->hasPermission('type-entity.view');
    }
}
