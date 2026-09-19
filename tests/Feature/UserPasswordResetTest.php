<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_reset_password_page_for_root_user(): void
    {
        // Find the root user (usually username === 'root')
        $rootUser = User::where('username', 'root')->firstOrFail();

        // Find the Admin role
        $adminRole = Role::where('name', 'Admin')->firstOrFail();

        // Create an admin user to perform the action
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($adminUser)
            ->get(route('users.resetPassword', $rootUser));

        $response->assertStatus(200);
        $response->assertViewIs('user.reset-password');
        $response->assertSee($rootUser->name);
    }

    public function test_admin_can_reset_root_user_password(): void
    {
        $rootUser = User::where('username', 'root')->firstOrFail();

        $adminRole = Role::where('name', 'Admin')->firstOrFail();
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $newPassword = 'NewSecretPassword123';

        $response = $this->actingAs($adminUser)
            ->post(route('users.updatePassword', $rootUser), [
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

        $response->assertRedirect(route('users.show', $rootUser));

        // Verify the password was indeed changed
        $rootUser->refresh();
        $this->assertTrue(Hash::check($newPassword, $rootUser->password));
    }

    public function test_admin_cannot_edit_root_user(): void
    {
        $rootUser = User::where('username', 'root')->firstOrFail();

        $adminRole = Role::where('name', 'Admin')->firstOrFail();
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Attempting to access the edit page should redirect back with error
        $response = $this->actingAs($adminUser)
            ->get(route('users.edit', $rootUser));

        $response->assertRedirect();

        // Attempting to update should redirect back with error
        $response = $this->actingAs($adminUser)
            ->put(route('users.update', $rootUser), [
                'name' => 'Updated Name',
                'role_id' => $adminRole->id,
                'phone' => '12345678',
                'status' => 'Active',
            ]);

        $response->assertRedirect();
        $this->assertNotSame('Updated Name', $rootUser->fresh()->name);
    }

    public function test_admin_cannot_disable_root_user(): void
    {
        $rootUser = User::where('username', 'root')->firstOrFail();

        $adminRole = Role::where('name', 'Admin')->firstOrFail();
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($adminUser)
            ->post(route('users.disable', $rootUser));

        $response->assertRedirect();

        $rootUser->refresh();
        $this->assertEquals('Active', $rootUser->status);
    }

    public function test_admin_cannot_delete_root_user(): void
    {
        $rootUser = User::where('username', 'root')->firstOrFail();

        $adminRole = Role::where('name', 'Admin')->firstOrFail();
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($adminUser)
            ->delete(route('users.destroy', $rootUser));

        $response->assertRedirect();

        // Verify root user still exists
        $this->assertDatabaseHas('users', [
            'username' => 'root',
        ]);
    }
}
