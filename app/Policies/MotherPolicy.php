<?php

namespace App\Policies;

use App\Models\Mother;
use App\Models\User;

class MotherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('mothers.view') || $user->hasPermission('mothers.index');
    }

    public function view(User $user, ?Mother $model = null): bool
    {
        return $user->hasPermission('mothers.view') || $user->hasPermission('mothers.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('mothers.create');
    }

    public function update(User $user, ?Mother $model = null): bool
    {
        return $user->hasPermission('mothers.update') || $user->hasPermission('mothers.edit');
    }

    public function delete(User $user, ?Mother $model = null): bool
    {
        return $user->hasPermission('mothers.delete') || $user->hasPermission('mothers.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('mothers.import') || $user->hasPermission('mothers.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('mothers.export') || $user->hasPermission('mothers.view');
    }
}
