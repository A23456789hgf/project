<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic permissions
        Permission::create(['name' => 'عرض المحافظات', 'slug' => 'governorates.view', 'module' => 'encoding', 'type' => 'page']);
        Permission::create(['name' => 'إنشاء محافظة', 'slug' => 'governorates.create', 'module' => 'encoding', 'type' => 'action']);
        Permission::create(['name' => 'تعديل محافظة', 'slug' => 'governorates.edit', 'module' => 'encoding', 'type' => 'action']);
        Permission::create(['name' => 'حذف محافظة', 'slug' => 'governorates.delete', 'module' => 'encoding', 'type' => 'action']);

        Permission::create(['name' => 'عرض المديريات', 'slug' => 'directorates.view', 'module' => 'encoding', 'type' => 'page']);
        Permission::create(['name' => 'إنشاء مديرية', 'slug' => 'directorates.create', 'module' => 'encoding', 'type' => 'action']);
        Permission::create(['name' => 'حذف مديرية', 'slug' => 'directorates.delete', 'module' => 'encoding', 'type' => 'action']);

        Permission::create(['name' => 'عرض القرى', 'slug' => 'villages.view', 'module' => 'encoding', 'type' => 'page']);
        Permission::create(['name' => 'عرض المجالات', 'slug' => 'domains.view', 'module' => 'encoding', 'type' => 'page']);
        Permission::create(['name' => 'عرض المانحين', 'slug' => 'donors.view', 'module' => 'encoding', 'type' => 'page']);
        Permission::create(['name' => 'عرض الجمعيات', 'slug' => 'associations.view', 'module' => 'encoding', 'type' => 'page']);
    }

    /**
     * Test that a user without permissions receives 403 Forbidden on configuration endpoints.
     */
    public function test_unauthorized_user_gets_403_forbidden_on_configuration_actions(): void
    {
        // Create a basic role with NO permissions
        $role = Role::create([
            'name' => 'TestNoPermissionsRole_'.uniqid(),
            'description' => 'Test role without permissions',
            'is_active' => true,
            'full_access' => false,
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        // Test GET index
        $this->get(route('governorates.index'))->assertStatus(403);
        $this->get(route('directorates.index'))->assertStatus(403);
        $this->get(route('villages.index'))->assertStatus(403);

        // Test POST store
        $this->post(route('governorates.store'), ['name' => 'Test Gov'])->assertStatus(403);
        $this->post(route('directorates.store'), ['name' => 'Test Dir'])->assertStatus(403);

        // Test GET create
        $this->get(route('governorates.create'))->assertStatus(403);
        $this->get(route('directorates.create'))->assertStatus(403);
    }

    /**
     * Test that an admin user with permissions can access configuration endpoints.
     */
    public function test_admin_user_can_access_configuration_endpoints(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Admin Role',
            'is_active' => true,
            'full_access' => true,
        ]);

        $adminUser = User::factory()->create([
            'role_id' => $role->id,
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);

        $this->get(route('governorates.index'))->assertStatus(200);
        $this->get(route('directorates.index'))->assertStatus(200);
        $this->get(route('villages.index'))->assertStatus(200);
        $this->get(route('domains.index'))->assertStatus(200);
        $this->get(route('donors.index'))->assertStatus(200);
        $this->get(route('associations.index'))->assertStatus(200);
    }
}
