<?php

namespace App\Policies;

use App\Models\Beneficiary;
use App\Models\User;

class BeneficiaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('beneficiaries.view') || $user->hasPermission('beneficiaries.index');
    }

    public function view(User $user, ?Beneficiary $model = null): bool
    {
        return $user->hasPermission('beneficiaries.view') || $user->hasPermission('beneficiaries.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('beneficiaries.create');
    }

    public function update(User $user, ?Beneficiary $model = null): bool
    {
        return $user->hasPermission('beneficiaries.update') || $user->hasPermission('beneficiaries.edit');
    }

    public function delete(User $user, ?Beneficiary $model = null): bool
    {
        return $user->hasPermission('beneficiaries.delete') || $user->hasPermission('beneficiaries.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('beneficiaries.import') || $user->hasPermission('beneficiaries.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('beneficiaries.export') || $user->hasPermission('beneficiaries.view');
    }
}
