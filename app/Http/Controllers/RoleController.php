<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Display a listing of the resource and permissions matrix.
     */
    public function index(Request $request)
    {
        // 1. Fetch Roles
        $roles = Role::withCount('users')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch All Permissions Grouped by Module
        $allPermissions = Permission::select('id', 'module', 'slug')->orderBy('module')->get();
        $categorized = $this->categorizePermissions($allPermissions);
        $allModuleNames = $allPermissions->pluck('module')->unique()->sort()->values();

        $moduleTranslations = $this->getModuleTranslations();
        $rolePermissions = [];
        $internalEntities = InternalEntity::active()->visibleToUser()->get();

        return view('roles.create', compact('allModuleNames', 'categorized', 'moduleTranslations', 'rolePermissions', 'internalEntities'));
    }

    /**
     * Store a newly created resource in storage.
     * Note: Enforces unique role names via validation.
     * Multiple roles are allowed to share the same permission sets.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'full_access' => 'boolean',
            'permissions' => 'nullable|array',
            'module_scopes' => 'nullable|array',
            'entity_id' => 'nullable|exists:internal_entities,id',
            'entity_display_scope' => 'nullable|array',
            'entity_add_scope' => 'nullable|array',
            'module_geo_scopes' => 'nullable|array',
            'entity_display_scope.internal_entities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
            'entity_display_scope.authorities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
            'entity_add_scope.internal_entities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
            'entity_add_scope.authorities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
        ], [
            'name.unique' => 'هذا الدور موجود مسبقاً',
            'name.required' => 'يرجى إدخال اسم الدور',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'full_access' => $request->boolean('full_access'),
                'module_scopes' => $request->input('module_scopes', []),
                'module_geo_scopes' => $request->input('module_geo_scopes', []),
                'entity_display_scope' => $request->input('entity_display_scope', []),
                'entity_add_scope' => $request->input('entity_add_scope', []),
            ]);

            // Save permissions
            $permissionIds = $request->input('permissions', []);
            if ($request->has('permissions_json') && ! empty($request->permissions_json)) {
                $decoded = json_decode($request->permissions_json, true);
                if (is_array($decoded)) {
                    $permissionIds = $decoded;
                }
            }
            $moduleScopes = $request->input('module_scopes', []);
            $entityId = $request->input('entity_id');

            // Automatically add scope permissions from dropdown selections
            if (! empty($moduleScopes)) {
                foreach ($moduleScopes as $module => $scope) {
                    if (in_array($scope, ['all', 'own', 'parallel', 'user'])) {
                        $scopePerm = Permission::where('slug', "{$module}.view-{$scope}")->first();
                        if ($scopePerm) {
                            $permissionIds[] = $scopePerm->id;
                        }
                    }
                }
                $permissionIds = array_unique($permissionIds);
            }

            // Automatically add geographic scope permissions based on entity_display_scope
            $entityDisplayScope = $request->input('entity_display_scope', []);
            if (! empty($entityDisplayScope)) {
                $scopeMapping = [
                    'all' => 'all',
                    'same_governorate' => 'governorate',
                    'same_directorate' => 'directorate',
                ];

                if (isset($entityDisplayScope['internal_entities'])) {
                    $rawScope = $entityDisplayScope['internal_entities'];
                    $mappedScope = $scopeMapping[$rawScope] ?? null;
                    if ($mappedScope) {
                        $scopePerm = Permission::where('slug', "stakeholders.scope-{$mappedScope}-internal")->first();
                        if ($scopePerm) {
                            $permissionIds[] = $scopePerm->id;
                        }
                    }
                }

                if (isset($entityDisplayScope['authorities'])) {
                    $rawScope = $entityDisplayScope['authorities'];
                    $mappedScope = $scopeMapping[$rawScope] ?? null;
                    if ($mappedScope) {
                        $scopePerm = Permission::where('slug', "stakeholders.scope-{$mappedScope}-external")->first();
                        if ($scopePerm) {
                            $permissionIds[] = $scopePerm->id;
                        }
                    }
                }
                $permissionIds = array_unique($permissionIds);
            }

            if (! empty($permissionIds) && is_array($permissionIds)) {
                $insertData = [];
                $now = now();
                foreach ($permissionIds as $permId) {
                    $insertData[] = [
                        'role_id' => $role->id,
                        'permission_id' => $permId,
                        'entity_id' => $entityId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                RolePermission::insert($insertData);
            }

            DB::commit();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Role',
                'model_id' => $role->id,
                'new_values' => $role->toArray(),
                'description' => 'تم إنشاء دور جديد: '.$role->name,
                'ip_address' => $request->ip(),
            ]);

            Log::info("Role created successfully: {$role->name} (ID: {$role->id}) by User ID: ".auth()->id());

            return redirect()->route('roles.index')->with('success', 'تم إضافة الدور الجديد وصلاحياته بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role: '.$e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الدور: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        if ($this->isAdminRole($role)) {
            abort(403, 'صلاحيات مدير النظام غير قابلة للتعديل.');
        }
        // Fetch All Permissions Grouped by Module
        $allPermissions = Permission::select('id', 'module', 'slug')->orderBy('module')->get();
        $categorized = $this->categorizePermissions($allPermissions);
        $allModuleNames = $allPermissions->pluck('module')->unique()->sort()->values();

        // Get current role permissions
        $rolePermissions = RolePermission::where('role_id', $role->id)->pluck('permission_id')->toArray();

        $moduleTranslations = $this->getModuleTranslations();
        $internalEntities = InternalEntity::active()->visibleToUser()->get();

        // Find common entity_id if all permissions belong to same entity
        $commonEntityId = null;
        if (count($rolePermissions) > 0) {
            $entityIds = RolePermission::where('role_id', $role->id)->pluck('entity_id')->unique();
            if ($entityIds->count() === 1) {
                $commonEntityId = $entityIds->first();
            }
        }

        return view('roles.edit', compact('role', 'allModuleNames', 'categorized', 'moduleTranslations', 'rolePermissions', 'internalEntities', 'commonEntityId'));
    }

    /**
     * Update the specified resource in storage.
     * Note: Role names must remain unique across the system.
     */
    public function update(Request $request, Role $role)
    {
        if ($this->isAdminRole($role)) {
            abort(403, 'صلاحيات مدير النظام غير قابلة للتعديل.');
        }
        $oldName = $role->name;

        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,'.$role->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'full_access' => 'boolean',
            'permissions' => 'nullable|array',
            'module_scopes' => 'nullable|array',
            'entity_id' => 'nullable|exists:internal_entities,id',
            'entity_display_scope' => 'nullable|array',
            'entity_add_scope' => 'nullable|array',
            'module_geo_scopes' => 'nullable|array',
            'entity_display_scope.internal_entities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
            'entity_display_scope.authorities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
            'entity_add_scope.internal_entities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
            'entity_add_scope.authorities' => 'nullable|string|in:all,same_governorate,same_directorate,none',
        ]);

        try {
            DB::beginTransaction();

            $role->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', $role->is_active),
                'full_access' => $request->boolean('full_access'),
                'module_scopes' => $request->input('module_scopes', []),
                'module_geo_scopes' => $request->input('module_geo_scopes', []),
                'entity_display_scope' => $request->input('entity_display_scope', []),
                'entity_add_scope' => $request->input('entity_add_scope', []),
            ]);

            // Update permissions
            RolePermission::where('role_id', $role->id)->delete();

            $permissionIds = $request->input('permissions', []);
            if ($request->has('permissions_json') && ! empty($request->permissions_json)) {
                $decoded = json_decode($request->permissions_json, true);
                if (is_array($decoded)) {
                    $permissionIds = $decoded;
                }
            }
            $moduleScopes = $request->input('module_scopes', []);
            $entityId = $request->input('entity_id');

            // Automatically add scope permissions from dropdown selections
            if (! empty($moduleScopes)) {
                foreach ($moduleScopes as $module => $scope) {
                    if (in_array($scope, ['all', 'own', 'parallel', 'user'])) {
                        $scopePerm = Permission::where('slug', "{$module}.view-{$scope}")->first();
                        if ($scopePerm) {
                            $permissionIds[] = $scopePerm->id;
                        }
                    }
                }
                $permissionIds = array_unique($permissionIds);
            }

            // Automatically add geographic scope permissions based on entity_display_scope
            $entityDisplayScope = $request->input('entity_display_scope', []);
            if (! empty($entityDisplayScope)) {
                $scopeMapping = [
                    'all' => 'all',
                    'same_governorate' => 'governorate',
                    'same_directorate' => 'directorate',
                ];

                if (isset($entityDisplayScope['internal_entities'])) {
                    $rawScope = $entityDisplayScope['internal_entities'];
                    $mappedScope = $scopeMapping[$rawScope] ?? null;
                    if ($mappedScope) {
                        $scopePerm = Permission::where('slug', "stakeholders.scope-{$mappedScope}-internal")->first();
                        if ($scopePerm) {
                            $permissionIds[] = $scopePerm->id;
                        }
                    }
                }

                if (isset($entityDisplayScope['authorities'])) {
                    $rawScope = $entityDisplayScope['authorities'];
                    $mappedScope = $scopeMapping[$rawScope] ?? null;
                    if ($mappedScope) {
                        $scopePerm = Permission::where('slug', "stakeholders.scope-{$mappedScope}-external")->first();
                        if ($scopePerm) {
                            $permissionIds[] = $scopePerm->id;
                        }
                    }
                }
                $permissionIds = array_unique($permissionIds);
            }

            if (! empty($permissionIds) && is_array($permissionIds)) {
                $insertData = [];
                $now = now();
                foreach ($permissionIds as $permId) {
                    $insertData[] = [
                        'role_id' => $role->id,
                        'permission_id' => $permId,
                        'entity_id' => $entityId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                RolePermission::insert($insertData);
            }

            DB::commit();
            User::incrementRolePermissionsVersion($role->id);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Role',
                'model_id' => $role->id,
                'old_values' => $role->getOriginal(),
                'new_values' => $role->toArray(),
                'description' => 'تم تحديث بيانات الدور: '.$role->name,
                'ip_address' => $request->ip(),
            ]);

            Log::info("Role updated successfully: {$role->name} (ID: {$role->id}) by User ID: ".auth()->id());

            return redirect()->route('roles.index')->with('success', 'تم تحديث الدور والصلاحيات بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update role (ID: {$role->id}): ".$e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الدور: '.$e->getMessage());
        }
    }

    /**
     * Toggle the status of a role.
     */
    public function toggleStatus(Role $role)
    {
        if ($this->isAdminRole($role)) {
            return redirect()->back()->with('error', 'لا يمكن تعطيل دور مدير النظام الأساسي');
        }

        $role->update(['is_active' => ! $role->is_active]);

        $statusAr = $role->is_active ? 'تفعيل' : 'تعطيل';
        $statusEn = $role->is_active ? 'enabled' : 'disabled';

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggle_status',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'description' => "تم {$statusAr} الدور: ".$role->name,
            'ip_address' => request()->ip(),
        ]);

        Log::info("Role status {$statusEn}: {$role->name} (ID: {$role->id}) by User ID: ".auth()->id());

        return redirect()->back()->with('success', "تم {$statusAr} الدور بنجاح");
    }

    /**
     * Update roles and permissions.
     */
    public function updatePermissions(Request $request)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'entity_display_scope' => 'nullable|array',
            'entity_add_scope' => 'nullable|array',
            'module_scopes' => 'nullable|array',
            'module_geo_scopes' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $permissions = $request->input('permissions', []);
            $entityDisplayScopes = $request->input('entity_display_scope', []);
            $entityAddScopes = $request->input('entity_add_scope', []);
            $moduleScopesRequest = $request->input('module_scopes', []);
            $moduleGeoScopesRequest = $request->input('module_geo_scopes', []);

            // Check if permissions are keyed by role name (from permissions matrix)
            // or if it's a simple array (from individual role edit)
            $isMultiRole = ! empty($permissions) && is_array(reset($permissions));

            if ($isMultiRole) {
                // Handle permissions matrix format: permissions[RoleName][] = [1, 2, 3]
                // and potentially scopes[RoleName][module] = scope
                foreach ($permissions as $roleName => $permissionIds) {
                    if (in_array(strtolower($roleName), ['admin', 'مدير النظام'])) {
                        continue;
                    }
                    $role = Role::where('name', $roleName)->first();

                    if (! $role) {
                        continue; // Skip if role not found
                    }

                    // Delete existing permissions for this role
                    RolePermission::where('role_id', $role->id)->delete();

                    // Insert new permissions
                    if (! empty($permissionIds) && is_array($permissionIds)) {
                        $permissionIds = array_unique($permissionIds);

                        // Automatically add geographic scope permissions based on entity_display_scope if provided for this role
                        // (Assuming matrix might send them per role, but if not, we use the global ones if intended)
                        // In the current matrix blade, they are global/single-role oriented.
                        // If multiple roles are edited at once, the matrix needs to be role-aware for scopes too.

                        $insertData = [];
                        $now = now();

                        foreach ($permissionIds as $permId) {
                            $insertData[] = [
                                'role_id' => $role->id,
                                'permission_id' => $permId,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }

                        RolePermission::insert($insertData);
                    }

                    // If the request contains scopes (usually when editing a single role via the matrix UI)
                    if (! empty($entityDisplayScopes)) {
                        $role->update(['entity_display_scope' => $entityDisplayScopes]);
                    }
                    if (! empty($entityAddScopes)) {
                        $role->update(['entity_add_scope' => $entityAddScopes]);
                    }
                    if (! empty($moduleScopesRequest)) {
                        $role->update(['module_scopes' => $moduleScopesRequest]);
                    }
                    if (! empty($moduleGeoScopesRequest)) {
                        $role->update(['module_geo_scopes' => $moduleGeoScopesRequest]);
                    }
                    User::incrementRolePermissionsVersion($role->id);
                }

                DB::commit();

                Log::info('Permissions matrix updated for all roles by User ID: '.auth()->id());

                return redirect()->route('roles.index')
                    ->with('success', 'تم تحديث صلاحيات جميع الأدوار بنجاح');

            } else {
                // Handle single role format (backward compatibility)
                $request->validate([
                    'role_id' => 'required|exists:roles,id',
                ]);

                $roleId = $request->role_id;
                $role = Role::findOrFail($roleId);
                if ($this->isAdminRole($role)) {
                    return redirect()->back()->with('error', 'صلاحيات مدير النظام غير قابلة للتعديل.');
                }
                $permissionIds = $permissions; // Already a simple array
                if ($request->has('permissions_json') && ! empty($request->permissions_json)) {
                    $decoded = json_decode($request->permissions_json, true);
                    if (is_array($decoded)) {
                        $permissionIds = $decoded;
                    }
                }
                $moduleScopes = $request->input('module_scopes', []);
                $moduleGeoScopes = $request->input('module_geo_scopes', []);
                $entityDisplayScope = $request->input('entity_display_scope', []);
                $entityAddScope = $request->input('entity_add_scope', []);

                // Delete all existing permissions for this role
                RolePermission::where('role_id', $role->id)->delete();

                // Automatically add scope permissions from dropdown selections
                if (! empty($moduleScopes)) {
                    foreach ($moduleScopes as $module => $scope) {
                        if (in_array($scope, ['all', 'own', 'parallel', 'user'])) {
                            $scopePerm = Permission::where('slug', "{$module}.view-{$scope}")->first();
                            if ($scopePerm) {
                                $permissionIds[] = $scopePerm->id;
                            }
                        }
                    }
                }

                // Automatically add geographic scope permissions based on entity_display_scope
                if (! empty($entityDisplayScope)) {
                    $scopeMapping = [
                        'all' => 'all',
                        'same_governorate' => 'governorate',
                        'same_directorate' => 'directorate',
                    ];

                    foreach (['internal_entities', 'authorities'] as $type) {
                        if (isset($entityDisplayScope[$type])) {
                            $rawScope = $entityDisplayScope[$type];
                            $mappedScope = $scopeMapping[$rawScope] ?? null;
                            $suffix = ($type === 'internal_entities') ? 'internal' : 'external';
                            if ($mappedScope) {
                                $scopePerm = Permission::where('slug', "stakeholders.scope-{$mappedScope}-{$suffix}")->first();
                                if ($scopePerm) {
                                    $permissionIds[] = $scopePerm->id;
                                }
                            }
                        }
                    }
                }

                // Insert new permissions
                if (! empty($permissionIds) && is_array($permissionIds)) {
                    $permissionIds = array_unique($permissionIds);
                    $insertData = [];
                    $now = now();

                    foreach ($permissionIds as $permId) {
                        $insertData[] = [
                            'role_id' => $role->id,
                            'permission_id' => $permId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    RolePermission::insert($insertData);
                }

                // Update scopes
                $role->update([
                    'module_scopes' => $moduleScopes,
                    'module_geo_scopes' => $moduleGeoScopes,
                    'entity_display_scope' => $entityDisplayScope,
                    'entity_add_scope' => $entityAddScope,
                ]);

                DB::commit();
                User::incrementRolePermissionsVersion($role->id);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'update_permissions',
                    'model_type' => 'Role',
                    'model_id' => $role->id,
                    'description' => 'تم تحديث صلاحيات الدور: '.$role->name,
                    'ip_address' => $request->ip(),
                ]);

                Log::info("Permissions updated for role: {$role->name} (ID: {$role->id}) by User ID: ".auth()->id());

                return redirect()->route('roles.index')
                    ->with('success', 'تم تحديث صلاحيات الدور بنجاح');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update permissions: '.$e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الصلاحيات: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($this->isAdminRole($role)) {
            return redirect()->back()->with('error', 'لا يمكن حذف دور مدير النظام الأساسي');
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين');
        }

        $roleName = $role->name;
        $roleId = $role->id;

        try {
            DB::transaction(function () use ($role) {
                RolePermission::where('role_id', $role->id)->delete();
                $role->delete();
            });

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Role',
                'model_id' => $roleId,
                'description' => 'تم حذف الدور: '.$roleName,
                'ip_address' => request()->ip(),
            ]);

            Log::info("Role deleted successfully: {$roleName} (ID: {$roleId}) by User ID: ".auth()->id());

            return redirect()->back()->with('success', 'تم حذف الدور وكافة صلاحياته المرتبطة');
        } catch (\Exception $e) {
            Log::error("Failed to delete role (ID: {$roleId}): ".$e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الدور: '.$e->getMessage());
        }
    }

    protected function isAdminRole($role): bool
    {
        return false;
    }

    /**
     * Categorize permissions by type (sidebars, pages, actions, scopes)
     */
    private function categorizePermissions($allPermissions)
    {
        $categorized = [
            'sidebars' => [],
            'pages' => [],
            'actions' => [],
            'scopes' => [],
        ];

        foreach ($allPermissions as $permission) {
            $slug = $permission->slug;
            $module = $permission->module;

            $type = 'actions';

            // Check for scope permissions (view-own, view-all, view-parallel, view-user)
            if (str_contains($slug, 'view-own') || str_contains($slug, 'view-all') || str_contains($slug, 'view-parallel') || str_contains($slug, 'view-user')) {
                $type = 'scopes';
            }
            // Check for sidebar permissions
            elseif (str_contains($slug, 'sidebar')) {
                $type = 'sidebars';
            }
            // Check for view permissions (but not view-own or view-all)
            elseif (str_contains($slug, 'view')) {
                $type = 'pages';
            }

            if (! isset($categorized[$type][$module])) {
                $categorized[$type][$module] = [];
            }
            $categorized[$type][$module][] = (object) [
                'id' => $permission->id,
                'slug' => $permission->slug,
            ];
        }

        return $categorized;
    }

    /**
     * Get module translation labels
     */
    private function getModuleTranslations()
    {
        return [
            'home' => 'الرئيسية والإحصائيات',
            'authentication' => 'الأمان والتحقق',
            'projects' => 'إدارة المشاريع',
            'projects-implementation' => 'مشاريع التنفيذ',
            'project-requests' => 'طلبات المشاريع',
            'project-drafts' => 'مسودات المشاريع',
            'project-files' => 'ملفات المشروع',
            'project-risks' => 'مخاطر المشروع',
            'project-outputs' => 'مخرجات المشروع',
            'project-activity' => 'نشاط وسجل المشروع',
            'project-documents' => 'وثائق المشاريع',
            'project-drafts-enhanced' => 'المسودات المحسنة',
            'executive-activities' => 'الأنشطة التنفيذية',
            'erp-integration' => 'التكامل مع ERPNext',
            'execution' => 'عمليات التنفيذ والمتابعة',
            'execution-log' => 'سجل عمليات التنفيذ',
            'schedule' => 'الجدول الزمني',
            'quality' => 'إدارة الجودة',
            'reports' => 'التقارير: لوحة التحكم الموحدة',
            'reports-implementation' => 'التقارير: ملخص التنفيذ الميداني',
            'reports-quality' => 'التقارير: مقاييس الجودة',
            'reports-financial' => 'التقارير: المؤشرات المالية للمشاريع',
            'reports-progress' => 'التقارير: تتبع الإنجاز والتقدم الزمني',
            'reports-erpnext-financial' => 'التقارير: التقرير المالي ERPNext',
            'reports-pl-expense-summary' => 'التقارير: ملخص الإيرادات والنفقات',
            'reports-profit-and-loss' => 'التقارير: الأرباح والخسائر',
            'reports-official-summary' => 'التقارير: التقرير الرسمي الشامل',
            'reports-stakeholders' => 'التقارير: أصحاب المصلحة والجهات',
            'reports-permissions' => 'التقارير: مراجعة صلاحيات النظام',
            'referrals' => 'إدارة الإحالات',
            'correspondence' => 'المراسلات الصادرة والواردة',
            'financial-justifications' => 'المبررات المالية',
            'messaging' => 'التواصل الداخلي',
            'department-reports' => 'تقارير الإدارات',
            'planning' => 'إدارة التخطيط',
            'reviews' => 'أعمال المراجعة',
            'internal-entities' => 'الجهات الداخلية',
            'entities' => 'الجهات والشركاء',
            'authorities' => 'الجهات الخارجية',
            'supervising-entities' => 'الجهات الإشرافية',
            'supervisors' => 'إدارة المشرفين',
            'governorates' => 'إدارة المحافظات',
            'directorates' => 'إدارة المديريات',
            'sub-areas' => 'إدارة السكن/العزل',
            'villages' => 'إدارة القرى/الأحياء',
            'users' => 'إدارة المستخدمين',
            'roles' => 'إدارة الأدوار',
            'roles-permissions' => 'صلاحيات النظام',
            'audit_logs' => 'سجلات الرقابة',
            'system-operations' => 'عمليات النظام',
            'programs' => 'إدارة البرامج',
            'domains' => 'المجالات الرئيسية',
            'subdomains' => 'المجالات الفرعية',
            'interventions' => 'إدارة التدخلات',
            'associations' => 'إدارة الجمعيات',
            'donors' => 'إدارة المانحين',
            'executors' => 'الجهات المنفذة',
            'funding-sources' => 'مصادر التمويل',
            'financing-types' => 'أنواع التمويل',
            'form-financing' => 'نماذج التمويل',
            'subfinancing-forms' => 'نماذج التمويل الفرعية',
            'funded-entities' => 'الجهات الممولة',
            'target-categories' => 'الفئات المستهدفة',
            'financial-items' => 'البنود المالية',
            'units' => 'إدارة الوحدات',
            'priorities' => 'إدارة الأولويات',
            'participation' => 'إدارة المشاركات',
            'main-routers' => 'الموجهات الرئيسية',
            'sub-routers' => 'الموجهات الفرعية',
            'beneficiaries' => 'إدارة المستفيدين',
            'beneficiary-groups' => 'فئات المستفيدين',
            'stages' => 'مراحل الاعتماد',
            'profile' => 'الملف الشخصي',
            'activity-history' => 'سجل العمليات التاريخي',
            'approvals' => 'الاعتمادات والموافقات',
            'audit-logs' => 'سجلات الرقابة والتدقيق',
            'configuration' => 'إعدادات النظام العامة',
            'entity-father' => 'الجهات الرئيسية (الأب)',
            'entity-scopes' => 'نطاقات العمل للجهات',
            'execution-procedures' => 'إجراءات التنفيذ المتبعة',
            'financing-forms' => 'نماذج التمويل المعتمدة',
            'notifications' => 'مركز الإشعارات',
            'procedure-budget-justifications' => 'تبريرات ميزانية الإجراءات',
            'plans' => 'خطط العمل والمشاريع',
        ];
    }
}
