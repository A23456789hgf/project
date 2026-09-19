<?php

namespace App\Policies;

use App\Models\SubRouter;
use App\Models\User;

class SubRouterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sub-routers.view') || $user->hasPermission('sub-routers.index');
    }

    public function view(User $user, ?SubRouter $model = null): bool
    {
        return $user->hasPermission('sub-routers.view') || $user->hasPermission('sub-routers.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sub-routers.create');
    }

    public function update(User $user, ?SubRouter $model = null): bool
    {
        return $user->hasPermission('sub-routers.update') || $user->hasPermission('sub-routers.edit');
    }

    public function delete(User $user, ?SubRouter $model = null): bool
    {
        return $user->hasPermission('sub-routers.delete') || $user->hasPermission('sub-routers.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('sub-routers.import') || $user->hasPermission('sub-routers.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('sub-routers.export') || $user->hasPermission('sub-routers.view');
    }
}
