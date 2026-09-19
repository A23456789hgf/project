<?php

namespace Tests\Feature;

use App\Enums\ApprovalStepStatus;
use App\Enums\EntityResponsibilityType;
use App\Enums\ProjectStatus;
use App\Models\EntityApprovalStage;
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

class ProjectApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected InternalEntity $childEntity;

    protected InternalEntity $parentEntity;

    protected InternalEntity $otherEntity;

    protected User $creatorUser;

    protected User $childReviewer;

    protected User $parentReviewer;

    protected User $outsiderUser;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('signatures/test_signature.png', 'fake_signature_binary_data');

        $this->approvalService = app(ApprovalService::class);

        // Seed Matrix Permissions
        PermissionResolver::clearCache();
        Cache::flush();
        $this->seed(PermissionsSeeder::class);

        $permissionSlugs = [
            'main_modules.projects',
            'main_modules.correspondence',
            'approvals.view',
            'approvals.approve',
            'approvals.reject',
            'approvals.request-action',
            'projects.submit',
            'projects.create',
            'projects.edit',
            'projects.view',
            'projects.view-details',
            'projects.approve',
            'projects.reject',
            'projects.refer',
            'referrals.view',
            'reviews.financial',
            'reviews.technical',
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

        // Roles
        $adminRole = Role::create([
            'name' => 'admin',
            'is_active' => true,
            'full_access' => true,
        ]);

        $reviewerRole = Role::create([
            'name' => 'Reviewer',
            'is_active' => true,
            'full_access' => true,
        ]);

        // Attach permissions to Reviewer Role
        $permissions = Permission::all();
        foreach ($permissions as $perm) {
            RolePermission::firstOrCreate([
                'role_id' => $reviewerRole->id,
                'permission_id' => $perm->id,
            ]);
        }

        User::incrementRolePermissionsVersion($reviewerRole->id);
        User::incrementRolePermissionsVersion($adminRole->id);

        // Entity Hierarchy: Parent (Root) -> Child (Creator)
        $this->parentEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الاتحاد العام (Root Entity)',
            'is_active' => true,
        ]);

        $this->childEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجمعية التابعة (Creator Child Entity)',
            'parent_id' => $this->parentEntity->id,
            'is_active' => true,
        ]);

        $this->otherEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'جهة خارجية أخرى',
            'is_active' => true,
        ]);

        // Users
        $this->creatorUser = User::withoutGlobalScopes()->create([
            'name' => 'منشئ المشروع',
            'username' => 'creator_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'creator_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->childEntity->id,
            'role_id' => $reviewerRole->id,
            'status' => 'Active',
        ]);

        $this->childReviewer = User::withoutGlobalScopes()->create([
            'name' => 'مراجع الجهة التابعة',
            'username' => 'child_rev_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'child_rev_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->childEntity->id,
            'role_id' => $reviewerRole->id,
            'signature_path' => 'signatures/test_signature.png',
            'status' => 'Active',
        ]);

        $this->parentReviewer = User::withoutGlobalScopes()->create([
            'name' => 'مراجع الاتحاد العام',
            'username' => 'parent_rev_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'parent_rev_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->parentEntity->id,
            'role_id' => $reviewerRole->id,
            'signature_path' => 'signatures/test_signature.png',
            'status' => 'Active',
        ]);

        $this->outsiderUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم من جهة غير مخولة',
            'username' => 'outsider_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'outsider_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->otherEntity->id,
            'role_id' => $reviewerRole->id,
            'signature_path' => 'signatures/test_signature.png',
            'status' => 'Active',
        ]);

        $this->adminUser = User::withoutGlobalScopes()->create([
            'name' => 'مدير النظام',
            'username' => 'admin_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'signature_path' => 'signatures/test_signature.png',
            'status' => 'Active',
        ]);

        // Entity Approval Stages Configuration
        EntityApprovalStage::create([
            'entity_id' => $this->childEntity->id,
            'stage' => EntityResponsibilityType::TechnicalReview->value,
            'stage_order' => 1,
            'responsible_user_id' => $this->childReviewer->id,
        ]);
        EntityApprovalStage::create([
            'entity_id' => $this->childEntity->id,
            'stage' => EntityResponsibilityType::FinancialReview->value,
            'stage_order' => 2,
            'responsible_user_id' => $this->childReviewer->id,
        ]);
        EntityApprovalStage::create([
            'entity_id' => $this->childEntity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => $this->childReviewer->id,
        ]);

        EntityApprovalStage::create([
            'entity_id' => $this->parentEntity->id,
            'stage' => EntityResponsibilityType::TechnicalReview->value,
            'stage_order' => 1,
            'responsible_user_id' => $this->parentReviewer->id,
        ]);
        EntityApprovalStage::create([
            'entity_id' => $this->parentEntity->id,
            'stage' => EntityResponsibilityType::FinancialReview->value,
            'stage_order' => 2,
            'responsible_user_id' => $this->parentReviewer->id,
        ]);
        EntityApprovalStage::create([
            'entity_id' => $this->parentEntity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => $this->parentReviewer->id,
        ]);
    }

    protected function createDraftProject(): Project
    {
        return Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع مسودة لاختبار الكنترولر',
            'form_number' => 'PRJ-'.rand(1000, 9999),
            'status' => ProjectStatus::Draft->value,
            'creator_entity_id' => $this->childEntity->id,
            'internal_entity_id' => $this->childEntity->id,
            'created_by_user_id' => $this->creatorUser->id,
            'created_by' => $this->creatorUser->id,
        ]);
    }

    /**
     * Scenario A: Creator can close Draft via HTTP layer.
     */
    public function test_scenario_a_creator_can_close_draft()
    {
        $project = $this->createDraftProject();
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->count());

        $response = $this->actingAs($this->creatorUser)
            ->postJson(route('projects.approval.submit', $project), [
                'notes' => 'جاهز للاعتماد',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $project->refresh();
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals(6, ProjectApproval::where('project_id', $project->id)->count());

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($activeStep);
        $this->assertEquals(1, $activeStep->step_order);
        $this->assertTrue($activeStep->isActive());
    }

    /**
     * Scenario B: Non-creator entity cannot close Draft.
     */
    public function test_scenario_b_non_creator_entity_cannot_close_draft()
    {
        $project = $this->createDraftProject();

        $response = $this->actingAs($this->outsiderUser)
            ->postJson(route('projects.approval.submit', $project), [
                'notes' => 'محاولة إغلاق غير مصرحة',
            ]);

        $response->assertStatus(403);

        $project->refresh();
        $this->assertEquals(ProjectStatus::Draft->value, $project->status);
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->count());
    }

    /**
     * Scenario C: Active entity user can approve.
     */
    public function test_scenario_c_active_entity_user_can_approve()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Step 1 is childEntity technical_review
        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.approve', $project), [
                'notes' => 'تمت المراجعة الفنية بنجاح',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_final' => false,
            ]);

        $project->refresh();
        $this->assertEquals(2, $project->current_stage_order);

        $step1 = ProjectApproval::where('project_id', $project->id)->where('step_order', 1)->first();
        $this->assertEquals(ApprovalStepStatus::Approved->value, $step1->status);
        $this->assertFalse($step1->is_active);

        $step2 = ProjectApproval::where('project_id', $project->id)->where('step_order', 2)->first();
        $this->assertEquals(ApprovalStepStatus::Pending->value, $step2->status);
        $this->assertTrue($step2->is_active);
    }

    /**
     * Scenario D: User from another entity cannot approve.
     */
    public function test_scenario_d_user_from_another_entity_cannot_approve()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Step 1 belongs to childEntity -> outsiderUser belongs to otherEntity
        $response = $this->actingAs($this->outsiderUser)
            ->postJson(route('projects.approval.approve', $project), [
                'notes' => 'محاولة موافقة غير مصرحة',
            ]);

        $response->assertStatus(403);

        $step1 = ProjectApproval::where('project_id', $project->id)->where('step_order', 1)->first();
        $this->assertEquals(ApprovalStepStatus::Pending->value, $step1->status);
        $this->assertTrue($step1->is_active);
    }

    /**
     * Scenario E: Locked step cannot be approved.
     */
    public function test_scenario_e_locked_step_cannot_be_approved()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Step 1 is active; Step 4 belongs to parentReviewer but is currently LOCKED
        $response = $this->actingAs($this->parentReviewer)
            ->postJson(route('projects.approval.approve', $project), [
                'notes' => 'محاولة اعتماد خطوة مقفلة',
            ]);

        // Should be forbidden because active step belongs to child entity and parent steps are locked
        $response->assertStatus(403);
    }

    /**
     * Scenario F: Reject requires reason (min 10 chars).
     */
    public function test_scenario_f_reject_requires_reason()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // 1. Missing reason -> 422
        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.reject', $project), [
                'reason' => 'قصير',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);

        // 2. Valid reason -> Success
        $validResponse = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.reject', $project), [
                'reason' => 'تم رفض المشروع لعدم مطابقة الشروط والمواصفات الفنية المعتمدة',
            ]);

        $validResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'project_status' => 'rejected',
            ]);

        $project->refresh();
        $this->assertEquals(ProjectStatus::Rejected->value, $project->status);
    }

    /**
     * Scenario G: Request Completion validates return_target.
     */
    public function test_scenario_g_request_completion_validates_return_target()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Invalid return_target -> 422
        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.requestAction', $project), [
                'reason' => 'يرجى مراجعة وتعديل الميزانية وإضافة النواقص',
                'return_target' => 'invalid_entity_target',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_target']);
    }

    /**
     * Scenario H: CreatorEntity completion + Resubmit works through HTTP layer.
     */
    public function test_scenario_h_creator_entity_completion_and_resubmit_works()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // 1. Request completion to creator entity
        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.requestAction', $project), [
                'reason' => 'يرجى استكمال الوثائق الفنية وتعديل خطة العمل',
                'return_target' => 'creator_entity',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'target' => 'creator_entity',
                'project_status' => 'rolled_back_for_review',
            ]);

        $project->refresh();
        $this->assertEquals(ProjectStatus::RolledBackForReview->value, $project->status);

        // 2. Resubmit by Creator
        $resubmitResponse = $this->actingAs($this->creatorUser)
            ->postJson(route('projects.approval.resubmit', $project), [
                'notes' => 'تم استكمال جميع الوثائق المطلوبة وتحديث الخطة',
            ]);

        $resubmitResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'project_status' => 'pending_approval',
            ]);

        $project->refresh();
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals(1, $project->current_stage_order);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($activeStep);
        $this->assertEquals(1, $activeStep->step_order);
        $this->assertTrue($activeStep->isActive());
    }

    /**
     * Scenario I: PreviousStep completion works through HTTP layer.
     */
    public function test_scenario_i_previous_step_completion_works()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Approve step 1 -> step 2 is active
        $this->approvalService->approveActiveStep($project, $this->childReviewer);
        $project->refresh();
        $this->assertEquals(2, $project->current_stage_order);

        // Step 2 reviewer requests return to previous step
        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.requestAction', $project), [
                'reason' => 'يرجى إعادة التدقيق الفني في بعض البنود',
                'return_target' => 'previous_step',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'target' => 'previous_step',
            ]);

        $project->refresh();
        $this->assertEquals(1, $project->current_stage_order);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($activeStep);
        $this->assertEquals(1, $activeStep->step_order);
        $this->assertTrue($activeStep->isActive());
    }

    /**
     * Scenario J: Consultation does not change active step.
     */
    public function test_scenario_j_consultation_does_not_change_active_step()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        $currentStageBefore = $project->current_stage;
        $currentOrderBefore = $project->current_stage_order;
        $statusBefore = $project->status;

        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approval.referral', $project), [
                'referred_entity_id' => $this->otherEntity->id,
                'referred_user_id' => $this->outsiderUser->id,
                'referral_text' => 'نرجو إبداء الرأي الفني والاستشاري حول المخططات',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $project->refresh();
        // Assert state remains completely unchanged
        $this->assertEquals($currentStageBefore, $project->current_stage);
        $this->assertEquals($currentOrderBefore, $project->current_stage_order);
        $this->assertEquals($statusBefore, $project->status);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($activeStep);
        $this->assertEquals(1, $activeStep->step_order);
        $this->assertTrue($activeStep->isActive());
    }

    /**
     * Scenario K: Legacy endpoints continue to respond according to their existing contract.
     */
    public function test_scenario_k_legacy_endpoints_continue_to_work()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // 1. Legacy approveProject with status approved
        $response = $this->actingAs($this->childReviewer)
            ->postJson(route('projects.approveProject', $project), [
                'status' => 'approved',
                'notes' => 'الموافقة عبر الواجهة القديمة',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'approval',
                'next_drop',
                'project_status',
            ])
            ->assertJson(['success' => true]);

        // 2. Legacy getApprovalStatus
        $statusResponse = $this->actingAs($this->childReviewer)
            ->getJson(route('projects.getApprovalStatus', $project));

        $statusResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'status']);

        // 3. Legacy getApprovalTimeline
        $timelineResponse = $this->actingAs($this->childReviewer)
            ->getJson(route('projects.getApprovalTimeline', $project));

        $timelineResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'timeline']);

        // 4. Legacy getProjectMovementLog
        $movementResponse = $this->actingAs($this->childReviewer)
            ->getJson(route('projects.getProjectMovementLog', $project));

        $movementResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'movement_log']);
    }
}
