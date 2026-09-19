<?php

namespace App\Policies;

use App\Models\SubArea;
use App\Models\User;

class SubAreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sub-areas.view') || $user->hasPermission('sub-areas.index');
    }

    public function view(User $user, ?SubArea $model = null): bool
    {
        return $user->hasPermission('sub-areas.view') || $user->hasPermission('sub-areas.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sub-areas.create');
    }

    public function update(User $user, ?SubArea $model = null): bool
    {
        return $user->hasPermission('sub-areas.update') || $user->hasPermission('sub-areas.edit');
    }

    public function delete(User $user, ?SubArea $model = null): bool
    {
        return $user->hasPermission('sub-areas.delete') || $user->hasPermission('sub-areas.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('sub-areas.import') || $user->hasPermission('sub-areas.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('sub-areas.export') || $user->hasPermission('sub-areas.view');
    }
}
