<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('domains.view') || $user->hasPermission('domains.index');
    }

    public function view(User $user, ?Domain $model = null): bool
    {
        return $user->hasPermission('domains.view') || $user->hasPermission('domains.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('domains.create');
    }

    public function update(User $user, ?Domain $model = null): bool
    {
        return $user->hasPermission('domains.update') || $user->hasPermission('domains.edit');
    }

    public function delete(User $user, ?Domain $model = null): bool
    {
        return $user->hasPermission('domains.delete') || $user->hasPermission('domains.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('domains.import') || $user->hasPermission('domains.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('domains.export') || $user->hasPermission('domains.view');
    }
}
