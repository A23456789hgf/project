<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ValueChainFinancing;

class ValueChainFinancingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('series_financing.view');
    }

    public function view(User $user, ValueChainFinancing $valueChainFinancing): bool
    {
        return $user->hasPermission('series_financing.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('series_financing.create');
    }

    public function update(User $user, ValueChainFinancing $valueChainFinancing): bool
    {
        return $user->hasPermission('series_financing.edit');
    }

    public function delete(User $user, ValueChainFinancing $valueChainFinancing): bool
    {
        return $user->hasPermission('series_financing.delete');
    }

    public function restore(User $user, ValueChainFinancing $valueChainFinancing): bool
    {
        return $user->hasPermission('series_financing.delete');
    }

    public function forceDelete(User $user, ValueChainFinancing $valueChainFinancing): bool
    {
        return $user->hasPermission('series_financing.delete');
    }
}
