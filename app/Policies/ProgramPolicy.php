<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    /**
     * عرض البرامج
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('programs.view');
    }

    /**
     * عرض برنامج واحد
     */
    public function view(User $user, ?Program $program = null): bool
    {
        return $user->hasPermission('programs.view');
    }

    /**
     * إنشاء برنامج
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('programs.create');
    }

    /**
     * تعديل برنامج
     */
    public function update(User $user, ?Program $program = null): bool
    {
        return $user->hasPermission('programs.edit');
    }

    /**
     * حذف برنامج
     */
    public function delete(User $user, ?Program $program = null): bool
    {
        return $user->hasPermission('programs.delete');
    }

    /**
     * تصدير البرامج
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('programs.export');
    }

    /**
     * استيراد البرامج
     */
    public function import(User $user): bool
    {
        return $user->hasPermission('programs.create');
    }
}
