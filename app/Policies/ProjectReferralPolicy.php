<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectReferral;
use App\Models\User;

class ProjectReferralPolicy
{
    /**
     * Determine whether the user can view any referrals.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() ||
               $user->hasPermission('referrals.view') ||
               $user->hasPermission('approvals.view');
    }

    /**
     * Determine whether the user can view a specific referral.
     */
    public function view(User $user, ProjectReferral $referral): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Actionable: Needs response
        if ($referral->isPending() && (int) $referral->referred_user_id === $user->id) {
            return true;
        }

        // Actionable: Needs closing
        if ($referral->status === 'responded' && $user->id === (int) $referral->referring_user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create referrals.
     */
    public function create(User $user, ?Project $project = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($project) {
            return $user->can('refer', $project);
        }

        return $user->hasPermission('referrals.create') ||
               $user->hasPermission('projects.refer') ||
               $user->hasPermission('approvals.view');
    }

    /**
     * Determine whether the user can respond to a referral.
     */
    public function respond(User $user, ProjectReferral $referral): bool
    {
        if (! $referral->isPending()) {
            return false;
        }

        return $user->id === (int) $referral->referred_user_id;
    }

    /**
     * Determine whether the user can close a referral.
     */
    public function close(User $user, ProjectReferral $referral): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $referral->isResponded()
            && $user->id === (int) $referral->referring_user_id;
    }
}
