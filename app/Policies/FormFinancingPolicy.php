<?php

namespace App\Policies;

use App\Models\FormFinancing;
use App\Models\User;

class FormFinancingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('form-financing.view') || $user->hasPermission('form-financing.index');
    }

    public function view(User $user, ?FormFinancing $model = null): bool
    {
        return $user->hasPermission('form-financing.view') || $user->hasPermission('form-financing.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('form-financing.create');
    }

    public function update(User $user, ?FormFinancing $model = null): bool
    {
        return $user->hasPermission('form-financing.update') || $user->hasPermission('form-financing.edit');
    }

    public function delete(User $user, ?FormFinancing $model = null): bool
    {
        return $user->hasPermission('form-financing.delete') || $user->hasPermission('form-financing.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('form-financing.import') || $user->hasPermission('form-financing.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('form-financing.export') || $user->hasPermission('form-financing.view');
    }
}
