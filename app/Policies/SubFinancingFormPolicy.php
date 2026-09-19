<?php

namespace App\Policies;

use App\Models\SubFinancingForm;
use App\Models\User;

class SubFinancingFormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('subfinancing-forms.view') || $user->hasPermission('subfinancing-forms.index');
    }

    public function view(User $user, ?SubFinancingForm $model = null): bool
    {
        return $user->hasPermission('subfinancing-forms.view') || $user->hasPermission('subfinancing-forms.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('subfinancing-forms.create');
    }

    public function update(User $user, ?SubFinancingForm $model = null): bool
    {
        return $user->hasPermission('subfinancing-forms.update') || $user->hasPermission('subfinancing-forms.edit');
    }

    public function delete(User $user, ?SubFinancingForm $model = null): bool
    {
        return $user->hasPermission('subfinancing-forms.delete') || $user->hasPermission('subfinancing-forms.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('subfinancing-forms.import') || $user->hasPermission('subfinancing-forms.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('subfinancing-forms.export') || $user->hasPermission('subfinancing-forms.view');
    }
}
