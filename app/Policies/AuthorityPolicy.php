<?php

namespace App\Policies;

use App\Models\Authority;
use App\Models\User;

class AuthorityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('authorities.view') || $user->hasPermission('authorities.index');
    }

    public function view(User $user, ?Authority $model = null): bool
    {
        return $user->hasPermission('authorities.view') || $user->hasPermission('authorities.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('authorities.create');
    }

    public function update(User $user, ?Authority $model = null): bool
    {
        return $user->hasPermission('authorities.update') || $user->hasPermission('authorities.edit');
    }

    public function delete(User $user, ?Authority $model = null): bool
    {
        return $user->hasPermission('authorities.delete') || $user->hasPermission('authorities.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('authorities.import') || $user->hasPermission('authorities.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('authorities.export') || $user->hasPermission('authorities.view');
    }
}
