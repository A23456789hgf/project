<?php

namespace App\Policies;

use App\Models\MainGuide;
use App\Models\User;

class MainGuidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('main-guides.view') || $user->hasPermission('main-guides.index');
    }

    public function view(User $user, ?MainGuide $model = null): bool
    {
        return $user->hasPermission('main-guides.view') || $user->hasPermission('main-guides.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('main-guides.create');
    }

    public function update(User $user, ?MainGuide $model = null): bool
    {
        return $user->hasPermission('main-guides.update') || $user->hasPermission('main-guides.edit');
    }

    public function delete(User $user, ?MainGuide $model = null): bool
    {
        return $user->hasPermission('main-guides.delete') || $user->hasPermission('main-guides.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('main-guides.import') || $user->hasPermission('main-guides.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('main-guides.export') || $user->hasPermission('main-guides.view');
    }
}
