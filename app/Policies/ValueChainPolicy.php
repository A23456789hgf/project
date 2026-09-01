<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ValueChain;

class ValueChainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('value_chains.view');
    }

    public function view(User $user, ValueChain $valueChain): bool
    {
        return $user->hasPermission('value_chains.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('value_chains.create');
    }

    public function update(User $user, ValueChain $valueChain): bool
    {
        return $user->hasPermission('value_chains.edit');
    }

    public function delete(User $user, ValueChain $valueChain): bool
    {
        return $user->hasPermission('value_chains.delete');
    }

    public function restore(User $user, ValueChain $valueChain): bool
    {
        return $user->hasPermission('value_chains.delete');
    }

    public function forceDelete(User $user, ValueChain $valueChain): bool
    {
        return $user->hasPermission('value_chains.delete');
    }
}
