<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

class EntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('entities.view') || $user->hasPermission('entities.index');
    }

    public function view(User $user, ?Entity $model = null): bool
    {
        return $user->hasPermission('entities.view') || $user->hasPermission('entities.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('entities.create');
    }

    public function update(User $user, ?Entity $model = null): bool
    {
        return $user->hasPermission('entities.update') || $user->hasPermission('entities.edit');
    }

    public function delete(User $user, ?Entity $model = null): bool
    {
        return $user->hasPermission('entities.delete') || $user->hasPermission('entities.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('entities.import') || $user->hasPermission('entities.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('entities.export') || $user->hasPermission('entities.view');
    }
}
