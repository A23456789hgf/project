<?php

namespace App\Policies;

use App\Models\FinancingType;
use App\Models\User;

class FinancingTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('financing-types.view') || $user->hasPermission('financing-types.index');
    }

    public function view(User $user, ?FinancingType $model = null): bool
    {
        return $user->hasPermission('financing-types.view') || $user->hasPermission('financing-types.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('financing-types.create');
    }

    public function update(User $user, ?FinancingType $model = null): bool
    {
        return $user->hasPermission('financing-types.update') || $user->hasPermission('financing-types.edit');
    }

    public function delete(User $user, ?FinancingType $model = null): bool
    {
        return $user->hasPermission('financing-types.delete') || $user->hasPermission('financing-types.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('financing-types.import') || $user->hasPermission('financing-types.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('financing-types.export') || $user->hasPermission('financing-types.view');
    }
}
