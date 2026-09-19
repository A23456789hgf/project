<?php

namespace App\Policies;

use App\Models\FundingSource;
use App\Models\User;

class FundingSourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('funding-sources.view') || $user->hasPermission('funding-sources.index');
    }

    public function view(User $user, ?FundingSource $model = null): bool
    {
        return $user->hasPermission('funding-sources.view') || $user->hasPermission('funding-sources.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('funding-sources.create');
    }

    public function update(User $user, ?FundingSource $model = null): bool
    {
        return $user->hasPermission('funding-sources.update') || $user->hasPermission('funding-sources.edit');
    }

    public function delete(User $user, ?FundingSource $model = null): bool
    {
        return $user->hasPermission('funding-sources.delete') || $user->hasPermission('funding-sources.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('funding-sources.import') || $user->hasPermission('funding-sources.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('funding-sources.export') || $user->hasPermission('funding-sources.view');
    }
}
