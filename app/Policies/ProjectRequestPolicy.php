<?php

namespace App\Policies;

use App\Models\ProjectRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('project-requests.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectRequest $projectRequest): bool
    {
        return $user->hasPermission('project-requests.view-details', $projectRequest) ||
               $user->hasPermission('project-requests.view', $projectRequest);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('project-requests.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectRequest $projectRequest): bool
    {
        return $user->hasPermission('project-requests.edit', $projectRequest);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectRequest $projectRequest): bool
    {
        return $user->hasPermission('project-requests.delete', $projectRequest);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, ProjectRequest $projectRequest): bool
    {
        return $user->hasPermission('project-requests.approve', $projectRequest);
    }

    /**
     * Determine whether the user can transfer the model to a project.
     */
    public function transfer(User $user, ProjectRequest $projectRequest): bool
    {
        return $user->hasPermission('project-requests.transfer', $projectRequest);
    }
}
