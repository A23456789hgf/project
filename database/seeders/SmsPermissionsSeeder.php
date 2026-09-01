<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SmsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'sms.manage',
            'sms.settings.view',
            'sms.settings.update',
            'sms.logs.view',
            'sms.manual.send',
        ];

        // Ensure permissions exist based on the matrix registry
        // Or simply call PermissionsSeeder to refresh everything from the matrix
        $this->call(PermissionsSeeder::class);

        // Optionally, assign these to the super admin role if it exists
        $adminRole = Role::where('name', 'مدير النظام')->orWhere('full_access', true)->first();
        if ($adminRole && ! $adminRole->full_access) {
            $permIds = Permission::whereIn('slug', $permissions)->pluck('id')->toArray();
            $adminRole->permissions()->syncWithoutDetaching($permIds);

            // clear cache for this role
            User::incrementRolePermissionsVersion($adminRole->id);
        }
    }
}
