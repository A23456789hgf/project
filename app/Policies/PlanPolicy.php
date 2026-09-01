<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('plans.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plan $plan): bool
    {
        return $user->hasPermission('plans.view', $plan);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('plans.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plan $plan): bool
    {
        return $user->hasPermission('plans.edit', $plan);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $user->hasPermission('plans.delete', $plan);
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user, ?Plan $plan = null): bool
    {
        if ($plan) {
            return $user->hasPermission('plans.export', $plan);
        }

        return $user->hasPermission('plans.export');
    }

    /**
     * Determine whether the user can print models.
     */
    public function print(User $user, ?Plan $plan = null): bool
    {
        if ($plan) {
            return $user->hasPermission('plans.print', $plan);
        }

        return $user->hasPermission('plans.print');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('plans.import');
    }

    public function batchPrint(User $user): bool
    {
        return $user->hasPermission('plans.batch-print');
    }

    public function comprehensiveBatchPrint(User $user): bool
    {
        return $user->hasPermission('plans.comprehensive-batch-print');
    }

    public function implementation(User $user, Plan $plan): bool
    {
        return $user->hasPermission('plans.implementation', $plan);
    }

    public function printImplementation(User $user, Plan $plan): bool
    {
        return $user->hasPermission('plans.print-implementation', $plan);
    }
}
