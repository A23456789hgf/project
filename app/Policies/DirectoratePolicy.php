<?php

namespace App\Policies;

use App\Models\Directorate;
use App\Models\User;

class DirectoratePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('directorates.view') || $user->hasPermission('directorates.index');
    }

    public function view(User $user, ?Directorate $model = null): bool
    {
        return $user->hasPermission('directorates.view') || $user->hasPermission('directorates.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('directorates.create');
    }

    public function update(User $user, ?Directorate $model = null): bool
    {
        return $user->hasPermission('directorates.update') || $user->hasPermission('directorates.edit');
    }

    public function delete(User $user, ?Directorate $model = null): bool
    {
        return $user->hasPermission('directorates.delete') || $user->hasPermission('directorates.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('directorates.import') || $user->hasPermission('directorates.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('directorates.export') || $user->hasPermission('directorates.view');
    }
}
