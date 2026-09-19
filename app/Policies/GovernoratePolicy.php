<?php

namespace App\Policies;

use App\Models\Governorate;
use App\Models\User;

class GovernoratePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('governorates.view') || $user->hasPermission('governorates.index');
    }

    public function view(User $user, ?Governorate $model = null): bool
    {
        return $user->hasPermission('governorates.view') || $user->hasPermission('governorates.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('governorates.create');
    }

    public function update(User $user, ?Governorate $model = null): bool
    {
        return $user->hasPermission('governorates.update') || $user->hasPermission('governorates.edit');
    }

    public function delete(User $user, ?Governorate $model = null): bool
    {
        return $user->hasPermission('governorates.delete') || $user->hasPermission('governorates.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('governorates.import') || $user->hasPermission('governorates.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('governorates.export') || $user->hasPermission('governorates.view');
    }
}
