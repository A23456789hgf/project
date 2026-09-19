<?php

namespace App\Policies;

use App\Models\MainRouter;
use App\Models\User;

class MainRouterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('main-routers.view') || $user->hasPermission('main-routers.index');
    }

    public function view(User $user, ?MainRouter $model = null): bool
    {
        return $user->hasPermission('main-routers.view') || $user->hasPermission('main-routers.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('main-routers.create');
    }

    public function update(User $user, ?MainRouter $model = null): bool
    {
        return $user->hasPermission('main-routers.update') || $user->hasPermission('main-routers.edit');
    }

    public function delete(User $user, ?MainRouter $model = null): bool
    {
        return $user->hasPermission('main-routers.delete') || $user->hasPermission('main-routers.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('main-routers.import') || $user->hasPermission('main-routers.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('main-routers.export') || $user->hasPermission('main-routers.view');
    }
}
