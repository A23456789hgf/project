<?php

namespace App\Policies;

use App\Models\Executor;
use App\Models\User;

class ExecutorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('executors.view') || $user->hasPermission('executors.index');
    }

    public function view(User $user, ?Executor $model = null): bool
    {
        return $user->hasPermission('executors.view') || $user->hasPermission('executors.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('executors.create');
    }

    public function update(User $user, ?Executor $model = null): bool
    {
        return $user->hasPermission('executors.update') || $user->hasPermission('executors.edit');
    }

    public function delete(User $user, ?Executor $model = null): bool
    {
        return $user->hasPermission('executors.delete') || $user->hasPermission('executors.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('executors.import') || $user->hasPermission('executors.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('executors.export') || $user->hasPermission('executors.view');
    }
}
