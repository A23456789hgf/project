<?php

namespace App\Policies;

use App\Models\User;

class SmsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sms.view') || $user->hasPermission('sms.index');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('sms.view') || $user->hasPermission('sms.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sms.create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('sms.update') || $user->hasPermission('sms.edit');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('sms.delete') || $user->hasPermission('sms.destroy');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('sms.import') || $user->hasPermission('sms.create');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('sms.export') || $user->hasPermission('sms.view');
    }
}
