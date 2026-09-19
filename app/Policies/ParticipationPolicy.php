<?php

namespace App\Policies;

use App\Models\Participation;
use App\Models\User;

class ParticipationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('participation.view') || $user->hasPermission('participation.index');
    }

    public function view(User $user, ?Participation $model = null): bool
    {
        return $user->hasPermission('participation.view') || $user->hasPermission('participation.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('participation.create');
    }

    public function update(User $user, ?Participation $model = null): bool
    {
        return $user->hasPermission('participation.update') || $user->hasPermission('participation.edit');
    }

    public function delete(User $user, ?Participation $model = null): bool
    {
        return $user->hasPermission('participation.delete') || $user->hasPermission('participation.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('participation.import') || $user->hasPermission('participation.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('participation.export') || $user->hasPermission('participation.view');
    }
}
