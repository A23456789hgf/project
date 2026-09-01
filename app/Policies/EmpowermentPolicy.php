<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmpowermentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any empowerment records.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('empowerment.view');
    }

    /**
     * Determine whether the user can view the empowerment record.
     */
    public function view(User $user, $record)
    {
        return $user->hasPermission('empowerment.view') &&
               $user->isInAdminScope($record) &&
               $user->isInGeoScope($record);
    }

    /**
     * Determine whether the user can create empowerment records.
     */
    public function create(User $user)
    {
        return $user->hasPermission('empowerment.create');
    }

    /**
     * Determine whether the user can update the empowerment record.
     */
    public function update(User $user, $record)
    {
        return $user->hasPermission('empowerment.edit') &&
               $user->isInAdminScope($record) &&
               $user->isInGeoScope($record);
    }

    /**
     * Determine whether the user can delete the empowerment record.
     */
    public function delete(User $user, $record)
    {
        return $user->hasPermission('empowerment.delete') &&
               $user->isInAdminScope($record) &&
               $user->isInGeoScope($record);
    }

    /**
     * Determine whether the user can update the status.
     */
    public function updateStatus(User $user, $record)
    {
        return $this->update($user, $record);
    }

    /**
     * Determine whether the user can view beneficiaries.
     */
    public function viewBeneficiaries(User $user, $record)
    {
        return $user->hasPermission('empowerment.beneficiaries.view') &&
               $user->isInAdminScope($record) &&
               $user->isInGeoScope($record);
    }

    /**
     * Determine whether the user can create a beneficiary.
     */
    public function createBeneficiary(User $user, $record)
    {
        return $user->hasPermission('empowerment.beneficiaries.create') &&
               $user->isInAdminScope($record) &&
               $user->isInGeoScope($record);
    }

    /**
     * Determine whether the user can delete a beneficiary.
     */
    public function deleteBeneficiary(User $user, $record)
    {
        return $user->hasPermission('empowerment.beneficiaries.delete') &&
               $user->isInAdminScope($record) &&
               $user->isInGeoScope($record);
    }
}
