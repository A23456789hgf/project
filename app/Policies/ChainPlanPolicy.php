<?php

namespace App\Policies;

use App\Models\ChainPlan;
use App\Models\User;

class ChainPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('chain_plans.view');
    }

    public function view(User $user, ChainPlan $chainPlan): bool
    {
        return $user->hasPermission('chain_plans.view', $chainPlan);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('chain_plans.create');
    }

    public function update(User $user, ChainPlan $chainPlan): bool
    {
        return $user->hasPermission('chain_plans.edit', $chainPlan);
    }

    public function delete(User $user, ChainPlan $chainPlan): bool
    {
        return $user->hasPermission('chain_plans.delete', $chainPlan);
    }

    public function export(User $user, ?ChainPlan $chainPlan = null): bool
    {
        if ($chainPlan) {
            return $user->hasPermission('chain_plans.export', $chainPlan);
        }

        return $user->hasPermission('chain_plans.export');
    }

    public function print(User $user, ?ChainPlan $chainPlan = null): bool
    {
        if ($chainPlan) {
            return $user->hasPermission('chain_plans.print', $chainPlan);
        }

        return $user->hasPermission('chain_plans.print');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('chain_plans.import');
    }

    public function batchPrint(User $user): bool
    {
        return $user->hasPermission('chain_plans.batch-print');
    }

    public function comprehensiveBatchPrint(User $user): bool
    {
        return $user->hasPermission('chain_plans.comprehensive-batch-print');
    }
}
