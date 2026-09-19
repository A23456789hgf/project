<?php

namespace Tests\Feature;

use App\Enums\ApprovalPhase;
use App\Enums\ProjectStatus;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\PermissionResolver;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsultationInboxTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected InternalEntity $referringEntity;

    protected InternalEntity $consultedEntity;

    protected InternalEntity $unrelatedEntity;

    protected User $referringUser;

    protected User $consultedUser;

    protected User $unrelatedUser;

    protected User $adminUser;

    protected Role $consultationRole;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('signatures/test_signature.png', 'fake_signature_binary_data');

        $this->approvalService = app(ApprovalService::class);

        PermissionResolver::clearCache();
        Cache::flush();
        $this->seed(PermissionsSeeder::class);

        $permissionSlugs = [
            'main_modules.projects',
            'approvals.view',
            'approvals.approve',
            'approvals.reject',
            'projects.view',
            'projects.refer',
            'referrals.view',
            'referrals.create',
            'referrals.respond',
        ];

        foreach ($permissionSlugs as $slug) {
            Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug,
                    'module' => explode('.', $slug)[0],
                    'type' => 'action',
                ]
            );
        }

        PermissionResolver::clearCache();
        Cache::flush();

        $this->consultationRole = Role::create([
            'name' => 'ConsultationRole',
            'is_active' => true,
            'full_access' => false,
        ]);

        foreach (Permission::all() as $perm) {
            RolePermission::firstOrCreate([
                'role_id' => $this->consultationRole->id,
                'permission_id' => $perm->id,
            ]);
        }

        $this->referringEntity = InternalEntity::factory()->create(['name' => 'Referring Entity', 'is_active' => true]);
        $this->consultedEntity = InternalEntity::factory()->create(['name' => 'Consulted Entity', 'is_active' => true]);
        $this->unrelatedEntity = InternalEntity::factory()->create(['name' => 'Unrelated Entity', 'is_active' => true]);

        $this->referringUser = User::factory()->create([
            'name' => 'Referring User',
            'entity_id' => $this->referringEntity->id,
            'role_id' => $this->consultationRole->id,
            'is_active' => true,
        ]);

        $this->consultedUser = User::factory()->create([
            'name' => 'Consulted User',
            'entity_id' => $this->consultedEntity->id,
            'role_id' => $this->consultationRole->id,
            'is_active' => true,
        ]);

        $this->unrelatedUser = User::factory()->create([
            'name' => 'Unrelated User',
            'entity_id' => $this->unrelatedEntity->id,
            'role_id' => $this->consultationRole->id,
            'is_active' => true,
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'entity_id' => null,
            'is_active' => true,
            'role_id' => null,
            'is_admin' => true,
        ]);
    }

    protected function createProjectWithReferral(): array
    {
        $project = Project::factory()->create([
            'project_name' => 'Consultation Test Project',
            'creator_entity_id' => $this->referringEntity->id,
            'created_by_user_id' => $this->referringUser->id,
            'status' => ProjectStatus::PendingApproval->value,
            'current_stage' => 'entity_'.$this->referringEntity->id.'_technical_review',
            'current_stage_order' => 1,
        ]);

        $approval = ProjectApproval::create([
            'project_id' => $project->id,
            'entity_id' => $this->referringEntity->id,
            'step_order' => 1,
            'status' => 'pending',
            'phase' => ApprovalPhase::TechnicalReview->value,
            'is_active' => true,
            'is_completed' => false,
            'drop' => 'entity_'.$this->referringEntity->id.'_technical_review',
        ]);

        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'نأمل التكرم بإبداء الرأي والملاحظات التخصصية حول متطلبات المشروع المرفقة.'
        );

        $referral->update(['referred_user_id' => $this->consultedUser->id]);

        return [$project, $approval, $referral->fresh()];
    }

    public function test_consulted_entity_user_sees_pending_consultation_in_my_action(): void
    {
        [$project, $approval, $referral] = $this->createProjectWithReferral();

        $response = $this->actingAs($this->consultedUser)->get(route('consultations.index', ['tab' => 'my_action']));

        $response->assertStatus(200);
        $referrals = $response->viewData('referrals');
        $this->assertTrue($referrals->contains('id', $referral->id));
        $this->assertTrue($referrals->contains('id', $referral->id));
    }

    public function test_referring_user_does_not_see_pending_consultation_in_my_action(): void
    {
        [$project, $approval, $referral] = $this->createProjectWithReferral();

        // 1. In 'my_action', sender should NOT see it because it requires action from the recipient
        $resAction = $this->actingAs($this->referringUser)->get(route('consultations.index', ['tab' => 'my_action']));
        $resAction->assertStatus(200);
        $this->assertFalse($resAction->viewData('referrals')->contains('id', $referral->id));
        $this->assertEquals(0, $resAction->viewData('myActionCount'));

        // 2. In 'sent' tab, sender DOES see it
        $resSent = $this->actingAs($this->referringUser)->get(route('consultations.index', ['tab' => 'sent']));
        $resSent->assertStatus(200);
        $this->assertFalse($resSent->viewData('referrals')->contains('id', $referral->id));
    }

    public function test_unrelated_user_does_not_see_consultation(): void
    {
        [$project, $approval, $referral] = $this->createProjectWithReferral();

        // Unrelated user sees 0 across tabs
        $resAction = $this->actingAs($this->unrelatedUser)->get(route('consultations.index', ['tab' => 'my_action']));
        $resAction->assertStatus(200);
        $this->assertFalse($resAction->viewData('referrals')->contains('id', $referral->id));
        $this->assertEquals(0, $resAction->viewData('myActionCount'));

        $resIncoming = $this->actingAs($this->unrelatedUser)->get(route('consultations.index', ['tab' => 'incoming']));
        $this->assertFalse($resIncoming->viewData('referrals')->contains('id', $referral->id));

        // Details show returns 403 Forbidden
        $resShow = $this->actingAs($this->unrelatedUser)->get(route('consultations.show', $referral));
        $resShow->assertStatus(403);
    }

    public function test_admin_user_sees_all_consultations(): void
    {
        [$project, $approval, $referral] = $this->createProjectWithReferral();

        $response = $this->actingAs($this->adminUser)->get(route('consultations.index', ['tab' => 'my_action']));

        $response->assertStatus(200);
        $this->assertTrue($response->viewData('referrals')->contains('id', $referral->id));
    }

    public function test_consultation_disappears_from_my_action_after_respond_and_remains_in_db(): void
    {
        [$project, $approval, $referral] = $this->createProjectWithReferral();

        // 1. Visible in my_action before response
        $resBefore = $this->actingAs($this->consultedUser)->get(route('consultations.index', ['tab' => 'my_action']));
        $this->assertTrue($resBefore->viewData('referrals')->contains('id', $referral->id));

        // 2. Respond to consultation
        $this->approvalService->respondToConsultation(
            $referral,
            'تمت مراجعة المواصفات ونؤكد الموافقة عليها مع تطبيق المعايير المعتمدة.',
            $this->consultedUser,
            'responded'
        );

        // 3. Disappears from my_action tab
        $resAfter = $this->actingAs($this->consultedUser)->get(route('consultations.index', ['tab' => 'my_action']));
        $this->assertFalse($resAfter->viewData('referrals')->contains('id', $referral->id));
        $this->assertEquals(0, $resAfter->viewData('myActionCount'));

        // 4. Appears in completed tab
        $resCompleted = $this->actingAs($this->consultedUser)->get(route('consultations.index', ['tab' => 'completed']));
        $this->assertFalse($resCompleted->viewData('referrals')->contains('id', $referral->id));

        // 5. Historical integrity: Record still exists in database
        $this->assertDatabaseHas('project_referrals', [
            'id' => $referral->id,
            'status' => 'responded',
            'responding_user_id' => $this->consultedUser->id,
        ]);
    }

    public function test_consultation_isolation_maintains_approval_workflow_and_project_status(): void
    {
        [$project, $approval, $referral] = $this->createProjectWithReferral();

        // Check project and approval state before response
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);
        $this->assertTrue($approval->fresh()->is_active);
        $this->assertEquals('pending', $approval->fresh()->status);

        // Respond
        $this->approvalService->respondToConsultation(
            $referral,
            'تم الرد على الاستشارة دون المساس بالاعتماد.',
            $this->consultedUser,
            'responded'
        );

        // Check isolation: project status and active approval must NOT change
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);
        $this->assertTrue($approval->fresh()->is_active);
        $this->assertEquals('pending', $approval->fresh()->status);
        $this->assertEquals(1, $project->fresh()->current_stage_order);
    }
}
