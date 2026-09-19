<?php

namespace App\Policies;

use App\Models\Supervisor;
use App\Models\User;

class SupervisorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('supervisors.view') || $user->hasPermission('supervisors.index');
    }

    public function view(User $user, ?Supervisor $model = null): bool
    {
        return $user->hasPermission('supervisors.view') || $user->hasPermission('supervisors.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('supervisors.create');
    }

    public function update(User $user, ?Supervisor $model = null): bool
    {
        return $user->hasPermission('supervisors.update') || $user->hasPermission('supervisors.edit');
    }

    public function delete(User $user, ?Supervisor $model = null): bool
    {
        return $user->hasPermission('supervisors.delete') || $user->hasPermission('supervisors.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('supervisors.import') || $user->hasPermission('supervisors.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('supervisors.export') || $user->hasPermission('supervisors.view');
    }
}
