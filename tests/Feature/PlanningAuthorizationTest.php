<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic permissions for plans
        Permission::create(['name' => 'عرض الخطط', 'slug' => 'plans.view', 'module' => 'planning', 'type' => 'page']);
        Permission::create(['name' => 'إنشاء خطة', 'slug' => 'plans.create', 'module' => 'planning', 'type' => 'action']);
        Permission::create(['name' => 'تعديل خطة', 'slug' => 'plans.edit', 'module' => 'planning', 'type' => 'action']);
        Permission::create(['name' => 'تعديل خطة (قياسي)', 'slug' => 'plans.update', 'module' => 'planning', 'type' => 'action']);
        Permission::create(['name' => 'حذف خطة', 'slug' => 'plans.delete', 'module' => 'planning', 'type' => 'action']);
        Permission::create(['name' => 'تصدير الخطط', 'slug' => 'plans.export', 'module' => 'planning', 'type' => 'action']);
        Permission::create(['name' => 'استيراد الخطط', 'slug' => 'plans.import', 'module' => 'planning', 'type' => 'action']);
        Permission::create(['name' => 'طباعة الخطة', 'slug' => 'plans.print', 'module' => 'planning', 'type' => 'action']);
    }

    /**
     * Test that an unauthorized user gets 403 Forbidden on planning endpoints.
     */
    public function test_unauthorized_user_gets_403_forbidden_on_planning_actions(): void
    {
        $role = Role::create([
            'name' => 'NoPlanningPermsRole_'.uniqid(),
            'description' => 'Role without planning permissions',
            'is_active' => true,
            'full_access' => false,
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        // GET index, create, import form
        $this->get(route('plans.index'))->assertStatus(403);
        $this->get(route('plans.create'))->assertStatus(403);
        $this->get(route('plans.show-import'))->assertStatus(403);
        $this->get(route('plans.export'))->assertStatus(403);
        $this->get(route('plans.batch-print'))->assertStatus(403);

        // POST store, process-import
        $this->post(route('plans.store'), [])->assertStatus(403);
        $this->post(route('plans.process-import'), [])->assertStatus(403);
    }

    /**
     * Test that an admin or user with permissions can access planning index.
     */
    public function test_authorized_user_can_access_planning_index(): void
    {
        $role = Role::create([
            'name' => 'AdminRole_'.uniqid(),
            'description' => 'Admin Role',
            'is_active' => true,
            'full_access' => true,
        ]);

        $adminUser = User::factory()->create([
            'role_id' => $role->id,
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);

        $this->get(route('plans.index'))->assertStatus(200);
        $this->get(route('plans.create'))->assertStatus(200);
        $this->get(route('plans.show-import'))->assertStatus(200);
    }
}
