<?php

namespace App\Policies;

use App\Models\ReportType;
use App\Models\User;

class ReportTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('report-type.view') || $user->hasPermission('report-type.index');
    }

    public function view(User $user, ?ReportType $model = null): bool
    {
        return $user->hasPermission('report-type.view') || $user->hasPermission('report-type.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('report-type.create');
    }

    public function update(User $user, ?ReportType $model = null): bool
    {
        return $user->hasPermission('report-type.update') || $user->hasPermission('report-type.edit');
    }

    public function delete(User $user, ?ReportType $model = null): bool
    {
        return $user->hasPermission('report-type.delete') || $user->hasPermission('report-type.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('report-type.import') || $user->hasPermission('report-type.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('report-type.export') || $user->hasPermission('report-type.view');
    }
}
