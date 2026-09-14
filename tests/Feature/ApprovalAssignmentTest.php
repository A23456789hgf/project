<?php

namespace Tests\Feature;

use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create basic structure if needed
    }

    public function test_assigned_user_can_see_and_act_on_approval()
    {
        $entity = InternalEntity::create(['name' => 'Test Entity']);
        
        $assignedUser = clone User::factory()->create(['entity_id' => $entity->id]);
        $otherUser = clone User::factory()->create(['entity_id' => $entity->id]);
        $adminRole = \App\Models\Role::create(['name' => 'admin']);
        $adminUser = clone User::factory()->create(['entity_id' => $entity->id, 'role_id' => $adminRole->id]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'status' => 'pending_approval'
        ]);

        $approval = ProjectApproval::create([
            'project_id' => $project->id,
            'entity_id' => $entity->id,
            'assigned_user_id' => $assignedUser->id,
            'status' => 'pending',
            'is_active' => true,
            'step_order' => 1,
            'approval_type' => 'approval',
        ]);

        // Assigned user can see it in inbox
        // Removed global Gate::before to enforce real policy checks

        $this->actingAs($assignedUser);
        $response = $this->get('/approvals');
        $response->assertStatus(200);
        
        $response->assertSee($project->project_name);

        // Assigned user can act on it
        $response = $this->post("/approvals/{$project->id}/approve", [
            'action' => 'approve',
            'notes' => 'LGTM'
        ]);
        $response->assertStatus(302); // Redirects back usually on success

        // Restore approval status for further tests
        $approval->update(['status' => 'pending']);

        // Other user cannot see it in inbox
        $this->actingAs($otherUser);
        $response = $this->get('/approvals');
        $response->assertDontSee($project->project_name);

        // Other user cannot act on it
        $response = $this->post("/approvals/{$project->id}/approve", [
            'action' => 'approve'
        ]);
        $response->assertStatus(403);

        // Admin can see it
        $this->actingAs($adminUser);
        $response = $this->get('/approvals');
        $response->assertSee($project->project_name);
        
        // Admin CANNOT act on it unless assigned
        $response = $this->post("/approvals/{$project->id}/approve", [
            'action' => 'approve'
        ]);
        $response->assertStatus(403);
    }

    public function test_unassigned_approval_is_invisible_to_normal_users()
    {
        $entity = InternalEntity::create(['name' => 'Test Entity']);
        
        $user = clone User::factory()->create(['entity_id' => $entity->id]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'status' => 'pending_approval'
        ]);

        $approval = ProjectApproval::create([
            'project_id' => $project->id,
            'entity_id' => $entity->id,
            'assigned_user_id' => null,
            'status' => 'pending',
            'is_active' => true,
            'step_order' => 1,
            'approval_type' => 'approval',
        ]);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if (in_array($ability, ['approvals.view', 'referrals.view'])) {
                return true;
            }
            return null;
        });

        // User cannot see it in inbox
        $this->actingAs($user);
        $response = $this->get('/approvals');
        $response->assertDontSee($project->project_name);
    }

    public function test_assigned_user_can_see_and_act_on_referral()
    {
        $entity = InternalEntity::create(['name' => 'Test Entity']);
        
        $assignedUser = clone User::factory()->create(['entity_id' => $entity->id]);
        $otherUser = clone User::factory()->create(['entity_id' => $entity->id]);
        $referringUser = clone User::factory()->create(['entity_id' => $entity->id]);

        $project = Project::create([
            'project_name' => 'Referral Project',
            'status' => 'pending_approval'
        ]);

        $referral = \App\Models\ProjectReferral::create([
            'project_id' => $project->id,
            'referring_entity_id' => $entity->id,
            'referring_user_id' => $referringUser->id,
            'referred_entity_id' => $entity->id,
            'referred_user_id' => $assignedUser->id,
            'referral_text' => 'Test referral',
            'status' => 'pending',
        ]);

        // Assigned user can see it in consultations
        $this->actingAs($assignedUser);
        $response = $this->get('/consultations');
        $response->assertStatus(200);
        
        $records = $response->original->getData()['referrals'];
        $this->assertTrue($records->contains('id', $referral->id));

        // Assigned user can act on it
        $response = $this->post("/consultations/{$referral->id}/respond", [
            'status' => 'responded',
            'response_text' => 'This is a valid response text.'
        ]);
        if ($response->status() !== 302) {
            dump(session()->all());
        }
        $response->assertStatus(302); // Redirects back on success

        // Other user cannot see it
        $this->actingAs($otherUser);
        $response = $this->get('/consultations');
        
        $records = $response->original->getData()['referrals'];
        $this->assertFalse($records->contains('id', $referral->id));

        // Other user cannot act on it
        $response = $this->postJson("/consultations/{$referral->id}/respond", [
            'status' => 'responded',
            'response_text' => 'This is a valid response text.'
        ]);
        $response->assertStatus(403);
    }
}
