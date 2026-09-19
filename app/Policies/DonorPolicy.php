<?php

namespace App\Policies;

use App\Models\Donor;
use App\Models\User;

class DonorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('donors.view') || $user->hasPermission('donors.index');
    }

    public function view(User $user, ?Donor $model = null): bool
    {
        return $user->hasPermission('donors.view') || $user->hasPermission('donors.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('donors.create');
    }

    public function update(User $user, ?Donor $model = null): bool
    {
        return $user->hasPermission('donors.update') || $user->hasPermission('donors.edit');
    }

    public function delete(User $user, ?Donor $model = null): bool
    {
        return $user->hasPermission('donors.delete') || $user->hasPermission('donors.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('donors.import') || $user->hasPermission('donors.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('donors.export') || $user->hasPermission('donors.view');
    }
}
