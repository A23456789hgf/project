<?php

namespace App\Policies;

use App\Models\Priority;
use App\Models\User;

class PriorityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('priorities.view') || $user->hasPermission('priorities.index');
    }

    public function view(User $user, ?Priority $model = null): bool
    {
        return $user->hasPermission('priorities.view') || $user->hasPermission('priorities.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('priorities.create');
    }

    public function update(User $user, ?Priority $model = null): bool
    {
        return $user->hasPermission('priorities.update') || $user->hasPermission('priorities.edit');
    }

    public function delete(User $user, ?Priority $model = null): bool
    {
        return $user->hasPermission('priorities.delete') || $user->hasPermission('priorities.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('priorities.import') || $user->hasPermission('priorities.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('priorities.export') || $user->hasPermission('priorities.view');
    }
}
