<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Village;

class VillagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('villages.view') || $user->hasPermission('villages.index');
    }

    public function view(User $user, ?Village $model = null): bool
    {
        return $user->hasPermission('villages.view') || $user->hasPermission('villages.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('villages.create');
    }

    public function update(User $user, ?Village $model = null): bool
    {
        return $user->hasPermission('villages.update') || $user->hasPermission('villages.edit');
    }

    public function delete(User $user, ?Village $model = null): bool
    {
        return $user->hasPermission('villages.delete') || $user->hasPermission('villages.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('villages.import') || $user->hasPermission('villages.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('villages.export') || $user->hasPermission('villages.view');
    }
}
