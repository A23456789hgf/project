<?php

namespace App\Policies;

use App\Models\FundedEntity;
use App\Models\User;

class FundedEntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('funded-entities.view') || $user->hasPermission('funded-entities.index');
    }

    public function view(User $user, ?FundedEntity $model = null): bool
    {
        return $user->hasPermission('funded-entities.view') || $user->hasPermission('funded-entities.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('funded-entities.create');
    }

    public function update(User $user, ?FundedEntity $model = null): bool
    {
        return $user->hasPermission('funded-entities.update') || $user->hasPermission('funded-entities.edit');
    }

    public function delete(User $user, ?FundedEntity $model = null): bool
    {
        return $user->hasPermission('funded-entities.delete') || $user->hasPermission('funded-entities.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('funded-entities.import') || $user->hasPermission('funded-entities.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('funded-entities.export') || $user->hasPermission('funded-entities.view');
    }
}
