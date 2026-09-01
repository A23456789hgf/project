<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Services\PermissionResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMatrixPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-matrix-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from _permissions_matrix.blade.php to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permissions synchronization...');

        $matrixPermissions = PermissionResolver::getPermissions();

        if (empty($matrixPermissions)) {
            $this->error('No permissions found in the matrix file.');

            return 1;
        }

        $matrixSlugs = array_column($matrixPermissions, 'slug');

        DB::beginTransaction();

        try {
            // 1. Mark or delete permissions that are NOT in the matrix
            $toDelete = Permission::whereNotIn('slug', $matrixSlugs)->get();
            if ($toDelete->count() > 0) {
                $this->warn("Found {$toDelete->count()} permissions not in matrix. Deleting...");
                foreach ($toDelete as $perm) {
                    $perm->delete();
                }
            }

            // 2. Add or update permissions from the matrix
            $adminRoles = Role::where('name', 'admin')->orWhere('full_access', true)->get();

            foreach ($matrixPermissions as $data) {
                $permission = Permission::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'name' => $data['name'],
                        'module' => $data['module'] ?? 'general',
                        'type' => $data['type'] ?? 'action',
                    ]
                );

                // Auto-assign to Admin and Full Access roles
                foreach ($adminRoles as $role) {
                    RolePermission::updateOrCreate([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }

            DB::commit();
            $this->info('Permissions synchronization completed successfully.');
            PermissionResolver::clearCache();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during synchronization: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
