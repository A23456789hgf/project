<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin' => [
                'dashboard.*',
                'projects.view-all',  // Ensure admin can view ALL projects
                'projects.*',
                'project-files.*',
                'project-drafts.*',
                'project-drafts-enhanced.*',
                'project-activity.*',
                'procedure-budget-justifications.*',
                'execution-procedures.*',
                'approvals.*',
                'execution.*',
                'configuration.*',
                'users.*',
                'audit-logs.*',
                'documents.*',
                'reports.*',
                'data.*',
                'settings.*',
                'schedule.*',
                'quality.*',
                // New Modules
                'governorates.*',
                'directorates.*',
                'sub-areas.*',
                'villages.*',
                'programs.*',
                'domains.*',
                'subdomains.*',
                'interventions.*',
                'donors.*',
                'funding-sources.*',
                'financing-types.*',
                'financial-items.*',
                'authorities.*',
                'associations.*',
                'units.*',
                'beneficiaries.*',
                'main-routers.*',
                'sub-routers.*',
                'priorities.*',
                'roles-permissions.*',
                'supervising-entities.*',
                'correspondence.*',
                'planning.*',
                'messaging.*',
                'department-reports.*',
                'tasks.*',
                'task.*',
                'requests_descend.*',
                'value-chains.*',
            ],
            'Manager' => [
                'dashboard.view',
                'dashboard.statistics',
                'projects.view',
                'projects.create',
                'projects.edit',
                'projects.delete',
                'projects.show',
                'projects.export',
                'approvals.submit',
                'approvals.view',
                'approvals.documentation',
                'approvals.technical',
                'approvals.reject',
                'approvals.request_action',
                'execution.view',
                'execution.create',
                'execution.edit',
                'execution.delete',
                'execution.financial_justification.submit',
                'execution.technical_justification.submit',
                'execution.technical_justification.approve',
                'execution.approve',
                'execution.reject',
                'configuration.view',
                'configuration.create',
                'configuration.edit',
                'configuration.export',
                'documents.view',
                'documents.upload',
                'documents.delete',
                'reports.view',
                'reports.export',
                'data.export',
                'schedule.view',
                'quality.view',
                'quality.edit',
                'correspondence.*',
            ],
            'Financer' => [
                'dashboard.view',
                'dashboard.statistics',
                'projects.view',
                'projects.show',
                'projects.export',
                'approvals.view',
                'approvals.financial',
                'approvals.reject',
                'approvals.request_action',
                'execution.view',
                'execution.financial_justification.approve',
                'configuration.view',
                'configuration.export',
                'documents.view',
                'reports.view',
                'reports.export',
                'reports.financial',
                'data.export',
            ],
            'Creator' => [
                'dashboard.view',
                'dashboard.statistics',
                'projects.view', // Note: Controller will filter to own projects
                'projects.create',
                'projects.show',
                'projects.export',
                'approvals.submit',
                'approvals.view',
                'execution.view',
                'execution.create',
                'execution.edit',
                'execution.financial_justification.submit',
                'execution.technical_justification.submit',
                'documents.view',
                'documents.upload',
                'documents.delete',
                'reports.view',
                'data.export',
                'schedule.view',
                'quality.view',
                'type-entity.view',
                'type-entity.create',
                'type-entity.edit',
                'type-entity.delete',
                'type-entity.sidebar',
                'type-entity.export',
            ],
            'Funder' => [
                'dashboard.view',
                'projects.view', // Note: Controller will filter to approved projects
                'projects.show',
                'projects.export',
                'documents.view',
                'reports.view',
                'reports.export',
                'data.export',
            ],
        ];

        foreach ($roles as $roleName => $permissionPatterns) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            foreach ($permissionPatterns as $pattern) {
                if (str_ends_with($pattern, '.*')) {
                    $module = substr($pattern, 0, -2);
                    $permissions = Permission::where('slug', 'like', $module.'.%')->get();
                } else {
                    $permissions = Permission::where('slug', $pattern)->get();
                }

                foreach ($permissions as $permission) {
                    RolePermission::firstOrCreate([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        }
    }
}
