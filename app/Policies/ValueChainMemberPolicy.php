<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ValueChainMember;

class ValueChainMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('value_chain_members.view');
    }

    public function view(User $user, ValueChainMember $valueChainMember): bool
    {
        return $user->hasPermission('value_chain_members.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('value_chain_members.create');
    }

    public function update(User $user, ValueChainMember $valueChainMember): bool
    {
        return $user->hasPermission('value_chain_members.edit');
    }

    public function delete(User $user, ValueChainMember $valueChainMember): bool
    {
        return $user->hasPermission('value_chain_members.delete');
    }

    public function restore(User $user, ValueChainMember $valueChainMember): bool
    {
        return $user->hasPermission('value_chain_members.delete');
    }

    public function forceDelete(User $user, ValueChainMember $valueChainMember): bool
    {
        return $user->hasPermission('value_chain_members.delete');
    }
}
