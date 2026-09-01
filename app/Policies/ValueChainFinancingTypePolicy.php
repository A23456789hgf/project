<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ValueChainFinancingType;

class ValueChainFinancingTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkDatabasePermission('value-chain-financing-types.view');
    }

    public function view(User $user, ValueChainFinancingType $model): bool
    {
        return $user->checkDatabasePermission('value-chain-financing-types.view', $model);
    }

    public function create(User $user): bool
    {
        return $user->checkDatabasePermission('value-chain-financing-types.create');
    }

    public function update(User $user, ValueChainFinancingType $model): bool
    {
        return $user->checkDatabasePermission('value-chain-financing-types.edit', $model);
    }

    public function delete(User $user, ValueChainFinancingType $model): bool
    {
        return $user->checkDatabasePermission('value-chain-financing-types.delete', $model);
    }
}
