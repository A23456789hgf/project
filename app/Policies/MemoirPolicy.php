<?php

namespace App\Policies;

use App\Models\Memoir;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemoirPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('memoirs.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Memoir $memoir): bool
    {
        return $user->hasPermission('memoirs.view', $memoir);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('memoirs.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Memoir $memoir): bool
    {
        return $user->hasPermission('memoirs.edit', $memoir);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Memoir $memoir): bool
    {
        return $user->hasPermission('memoirs.delete', $memoir);
    }
}
