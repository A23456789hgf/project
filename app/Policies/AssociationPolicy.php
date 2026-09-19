<?php

namespace App\Policies;

use App\Models\Association;
use App\Models\User;

class AssociationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('associations.view') || $user->hasPermission('associations.index');
    }

    public function view(User $user, ?Association $model = null): bool
    {
        return $user->hasPermission('associations.view') || $user->hasPermission('associations.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('associations.create');
    }

    public function update(User $user, ?Association $model = null): bool
    {
        return $user->hasPermission('associations.update') || $user->hasPermission('associations.edit');
    }

    public function delete(User $user, ?Association $model = null): bool
    {
        return $user->hasPermission('associations.delete') || $user->hasPermission('associations.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('associations.import') || $user->hasPermission('associations.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('associations.export') || $user->hasPermission('associations.view');
    }
}
