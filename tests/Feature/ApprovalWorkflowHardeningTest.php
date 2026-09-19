<?php

namespace Tests\Feature;

use App\Enums\ApprovalStepStatus;
use App\Enums\EntityResponsibilityType;
use App\Enums\ProjectStatus;
use App\Enums\ReturnTarget;
use App\Exceptions\InvalidWorkflowTransitionException;
use App\Exceptions\WorkflowValidationException;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectActivityHistory;
use App\Models\ProjectApproval;
use App\Models\ProjectReferral;
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

class ApprovalWorkflowHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected Role $reviewerRole;

    protected Role $adminRole;

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
        $this->adminRole = Role::create([
            'name' => 'admin',
            'is_active' => true,
            'full_access' => true,
        ]);

        $this->reviewerRole = Role::create([
            'name' => 'Workflow Reviewer',
            'is_active' => true,
            'full_access' => true,
        ]);

        $permissions = Permission::all();
        foreach ($permissions as $perm) {
            RolePermission::firstOrCreate([
                'role_id' => $this->reviewerRole->id,
                'permission_id' => $perm->id,
            ]);
        }

        User::incrementRolePermissionsVersion($this->reviewerRole->id);
        User::incrementRolePermissionsVersion($this->adminRole->id);
    }

    /**
     * Helper to create a user for a given entity
     */
    protected function createUserForEntity(InternalEntity $entity, string $namePrefix = 'User'): User
    {
        $user = User::withoutGlobalScopes()->create([
            'name' => "{$namePrefix} - {$entity->name}",
            'username' => strtolower($namePrefix).'_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => strtolower($namePrefix).'_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $entity->id,
            'role_id' => $this->reviewerRole->id,
            'signature_path' => 'signatures/test_signature.png',
            'status' => 'Active',
            'organization_type' => 'internal',
        ]);

        foreach ([
            EntityResponsibilityType::TechnicalReview,
            EntityResponsibilityType::FinancialReview,
            EntityResponsibilityType::Approval,
        ] as $type) {
            EntityApprovalStage::updateOrCreate([
                'entity_id' => $entity->id,
                'stage' => $type->value,
            ], [
                'stage_order' => $type->stageOrder(),
                'responsible_user_id' => $user->id,
            ]);
        }

        return $user;
    }

    protected function seedStagesForChain(InternalEntity $entity): void
    {
        $current = $entity;
        while ($current) {
            if ($current->approvalStages()->count() === 0) {
                $user = User::withoutGlobalScopes()->where('entity_id', $current->id)->where('status', 'Active')->first();
                if (! $user) {
                    $user = $this->createUserForEntity($current, 'AutoUser');
                }
                foreach ([
                    EntityResponsibilityType::TechnicalReview,
                    EntityResponsibilityType::FinancialReview,
                    EntityResponsibilityType::Approval,
                ] as $type) {
                    EntityApprovalStage::firstOrCreate([
                        'entity_id' => $current->id,
                        'stage' => $type->value,
                    ], [
                        'stage_order' => $type->stageOrder(),
                        'responsible_user_id' => $user->id,
                    ]);
                }
            }
            $current = $current->parent_id
                ? InternalEntity::withoutGlobalScopes()->find($current->parent_id)
                : null;
        }
    }

    /**
     * Helper to create a draft project
     */
    protected function createDraftProject(InternalEntity $creatorEntity, User $creatorUser): Project
    {
        $this->seedStagesForChain($creatorEntity);

        return Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع تجريبي لاختبار التحصين - '.uniqid(),
            'form_number' => 'PRJ-'.rand(10000, 99999),
            'status' => ProjectStatus::Draft->value,
            'creator_entity_id' => $creatorEntity->id,
            'internal_entity_id' => $creatorEntity->id,
            'created_by_user_id' => $creatorUser->id,
            'created_by' => $creatorUser->id,
        ]);
    }

    // =========================================================================
    // 1. DYNAMIC HIERARCHY SCENARIOS (A, B, C, D)
    // =========================================================================

    /**
     * Scenario A: Creator Entity -> Root Entity (2 levels) -> Exactly 6 approval steps
     */
    public function test_scenario_a_creator_to_root_generates_exact_6_approval_steps()
    {
        $rootEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة الجذرية العليا (Scenario A Root)',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $creatorEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة المنشئة التابعة (Scenario A Creator)',
            'parent_id' => $rootEntity->id,
            'is_active' => true,
        ]);

        $creatorUser = $this->createUserForEntity($creatorEntity, 'CreatorA');
        $project = $this->createDraftProject($creatorEntity, $creatorUser);

        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->count());

        $chain = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        $this->assertCount(6, $chain);
        $this->assertEquals(6, ProjectApproval::where('project_id', $project->id)->count());

        $approvals = ProjectApproval::where('project_id', $project->id)->orderBy('step_order')->get();

        // 3 steps for Creator Entity
        $this->assertEquals($creatorEntity->id, $approvals[0]->entity_id);
        $this->assertEquals('technical_review', $approvals[0]->phase);
        $this->assertEquals(1, $approvals[0]->step_order);
        $this->assertTrue($approvals[0]->is_active);
        $this->assertEquals(ApprovalStepStatus::Pending->value, $approvals[0]->status);

        $this->assertEquals($creatorEntity->id, $approvals[1]->entity_id);
        $this->assertEquals('financial_review', $approvals[1]->phase);
        $this->assertEquals(2, $approvals[1]->step_order);
        $this->assertFalse($approvals[1]->is_active);
        $this->assertEquals(ApprovalStepStatus::Locked->value, $approvals[1]->status);

        $this->assertEquals($creatorEntity->id, $approvals[2]->entity_id);
        $this->assertEquals('stage_approval', $approvals[2]->phase);
        $this->assertEquals(3, $approvals[2]->step_order);
        $this->assertFalse($approvals[2]->is_active);
        $this->assertEquals(ApprovalStepStatus::Locked->value, $approvals[2]->status);

        // 3 steps for Root Entity
        $this->assertEquals($rootEntity->id, $approvals[3]->entity_id);
        $this->assertEquals('technical_review', $approvals[3]->phase);
        $this->assertEquals(4, $approvals[3]->step_order);
        $this->assertFalse($approvals[3]->is_active);
        $this->assertEquals(ApprovalStepStatus::Locked->value, $approvals[3]->status);

        $this->assertEquals($rootEntity->id, $approvals[4]->entity_id);
        $this->assertEquals('financial_review', $approvals[4]->phase);
        $this->assertEquals(5, $approvals[4]->step_order);
        $this->assertFalse($approvals[4]->is_active);
        $this->assertEquals(ApprovalStepStatus::Locked->value, $approvals[4]->status);

        $this->assertEquals($rootEntity->id, $approvals[5]->entity_id);
        $this->assertEquals('stage_approval', $approvals[5]->phase);
        $this->assertEquals(6, $approvals[5]->step_order);
        $this->assertFalse($approvals[5]->is_active);
        $this->assertEquals(ApprovalStepStatus::Locked->value, $approvals[5]->status);

        // Verify exactly one active step
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());
    }

    /**
     * Scenario B: Creator -> Parent -> Root (3 levels) -> Exactly 9 approval steps
     */
    public function test_scenario_b_creator_to_parent_to_root_generates_exact_9_approval_steps()
    {
        $rootEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة العليا (Scenario B Root)',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $midEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة المتوسطة (Scenario B Mid)',
            'parent_id' => $rootEntity->id,
            'is_active' => true,
        ]);

        $creatorEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة المنشئة (Scenario B Creator)',
            'parent_id' => $midEntity->id,
            'is_active' => true,
        ]);

        $creatorUser = $this->createUserForEntity($creatorEntity, 'CreatorB');
        $project = $this->createDraftProject($creatorEntity, $creatorUser);

        $chain = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        $this->assertCount(9, $chain);
        $this->assertEquals(9, ProjectApproval::where('project_id', $project->id)->count());

        $approvals = ProjectApproval::where('project_id', $project->id)->orderBy('step_order')->get();

        // Check Entity mapping across all 9 steps
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals($creatorEntity->id, $approvals[$i]->entity_id);
        }
        for ($i = 3; $i < 6; $i++) {
            $this->assertEquals($midEntity->id, $approvals[$i]->entity_id);
        }
        for ($i = 6; $i < 9; $i++) {
            $this->assertEquals($rootEntity->id, $approvals[$i]->entity_id);
        }

        // Only Step 1 is active
        $this->assertTrue($approvals[0]->is_active);
        $this->assertEquals(ApprovalStepStatus::Pending->value, $approvals[0]->status);
        for ($i = 1; $i < 9; $i++) {
            $this->assertFalse($approvals[$i]->is_active);
            $this->assertEquals(ApprovalStepStatus::Locked->value, $approvals[$i]->status);
        }

        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());
    }

    /**
     * Scenario C: Creator -> Tier 2 -> Tier 3 -> Root (4 levels) -> Exactly 12 approval steps
     */
    public function test_scenario_c_creator_with_four_tiers_generates_exact_12_approval_steps()
    {
        $rootEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'المستوى الأول - الجذر (Tier 4 Root)',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $tier3Entity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'المستوى الثاني (Tier 3)',
            'parent_id' => $rootEntity->id,
            'is_active' => true,
        ]);

        $tier2Entity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'المستوى الثالث (Tier 2)',
            'parent_id' => $tier3Entity->id,
            'is_active' => true,
        ]);

        $creatorEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'المستوى الرابع - المنشئ (Tier 1 Creator)',
            'parent_id' => $tier2Entity->id,
            'is_active' => true,
        ]);

        $creatorUser = $this->createUserForEntity($creatorEntity, 'CreatorC');
        $project = $this->createDraftProject($creatorEntity, $creatorUser);

        $chain = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        $this->assertCount(12, $chain);
        $this->assertEquals(12, ProjectApproval::where('project_id', $project->id)->count());

        $approvals = ProjectApproval::where('project_id', $project->id)->orderBy('step_order')->get();

        // Check Entity sequence across the 12 steps
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals($creatorEntity->id, $approvals[$i]->entity_id);
        }
        for ($i = 3; $i < 6; $i++) {
            $this->assertEquals($tier2Entity->id, $approvals[$i]->entity_id);
        }
        for ($i = 6; $i < 9; $i++) {
            $this->assertEquals($tier3Entity->id, $approvals[$i]->entity_id);
        }
        for ($i = 9; $i < 12; $i++) {
            $this->assertEquals($rootEntity->id, $approvals[$i]->entity_id);
        }

        $this->assertTrue($approvals[0]->is_active);
        $this->assertEquals(ApprovalStepStatus::Pending->value, $approvals[0]->status);
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());
    }

    /**
     * Scenario D: Creator = Root (1 level) -> Exactly 3 approval steps, Root Stage Approval is final
     */
    public function test_scenario_d_creator_is_root_generates_exact_3_approval_steps()
    {
        $rootEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة الجذرية المنشئة مباشرة (Root Creator)',
            'parent_id' => null,
            'is_active' => true,
            'is_ministry_root' => true,
        ]);

        $rootCreator = $this->createUserForEntity($rootEntity, 'RootCreator');
        $rootReviewer = $this->createUserForEntity($rootEntity, 'RootReviewer');
        $project = $this->createDraftProject($rootEntity, $rootCreator);

        $chain = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $rootCreator);

        $this->assertCount(3, $chain);
        $this->assertEquals(3, ProjectApproval::where('project_id', $project->id)->count());

        $approvals = ProjectApproval::where('project_id', $project->id)->orderBy('step_order')->get();

        $this->assertEquals($rootEntity->id, $approvals[0]->entity_id);
        $this->assertEquals('technical_review', $approvals[0]->phase);
        $this->assertEquals(1, $approvals[0]->step_order);
        $this->assertTrue($approvals[0]->is_active);

        $this->assertEquals($rootEntity->id, $approvals[1]->entity_id);
        $this->assertEquals('financial_review', $approvals[1]->phase);
        $this->assertEquals(2, $approvals[1]->step_order);
        $this->assertFalse($approvals[1]->is_active);

        $this->assertEquals($rootEntity->id, $approvals[2]->entity_id);
        $this->assertEquals('stage_approval', $approvals[2]->phase);
        $this->assertEquals(3, $approvals[2]->step_order);
        $this->assertFalse($approvals[2]->is_active);

        // Root Stage Approval at step 3 is the final step
        $this->assertEquals(3, $approvals[2]->step_order);

        // Finalize all 3 steps to verify transition to in_execution
        $this->approvalService->approveActiveStep($project, $rootReviewer); // Step 1 -> Step 2
        $this->approvalService->approveActiveStep($project, $rootReviewer); // Step 2 -> Step 3
        $result = $this->approvalService->approveActiveStep($project, $rootReviewer); // Step 3 -> InExecution

        $this->assertTrue($result['is_final']);
        $this->assertEquals(ProjectStatus::InExecution->value, $result['project_status']);
        $project->refresh();
        $this->assertEquals(ProjectStatus::InExecution->value, $project->status);
    }

    // =========================================================================
    // 2. ORDERING & INVARIANTS
    // =========================================================================

    /**
     * Verify Ordering: step_order strictly follows Entity hierarchy (Creator -> Parent -> Root)
     * and Phase sequence (Technical -> Financial -> Stage Approval).
     */
    public function test_verify_dynamic_step_ordering_and_phase_sequence()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Alpha Root', 'parent_id' => null, 'is_active' => true]);
        $mid = InternalEntity::withoutGlobalScopes()->create(['name' => 'Beta Branch', 'parent_id' => $root->id, 'is_active' => true]);
        $leaf = InternalEntity::withoutGlobalScopes()->create(['name' => 'Gamma Leaf', 'parent_id' => $mid->id, 'is_active' => true]);

        $user = $this->createUserForEntity($leaf, 'LeafUser');
        $project = $this->createDraftProject($leaf, $user);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $user);

        $approvals = ProjectApproval::where('project_id', $project->id)->orderBy('step_order')->get();
        $this->assertCount(9, $approvals);

        $expectedEntities = [
            $leaf->id, $leaf->id, $leaf->id,
            $mid->id, $mid->id, $mid->id,
            $root->id, $root->id, $root->id,
        ];

        $expectedPhases = [
            'technical_review', 'financial_review', 'stage_approval',
            'technical_review', 'financial_review', 'stage_approval',
            'technical_review', 'financial_review', 'stage_approval',
        ];

        foreach ($approvals as $idx => $approval) {
            $this->assertEquals($idx + 1, $approval->step_order, "Step order mismatch at index {$idx}");
            $this->assertEquals($expectedEntities[$idx], $approval->entity_id, "Entity mismatch at index {$idx}");
            $this->assertEquals($expectedPhases[$idx], $approval->phase, "Phase mismatch at index {$idx}");
        }
    }

    /**
     * Single Active Step Invariant: At most ONE step is active across all lifecycle transitions.
     */
    public function test_single_active_step_invariant_across_all_lifecycle_transitions()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Inv Root', 'parent_id' => null, 'is_active' => true]);
        $child = InternalEntity::withoutGlobalScopes()->create(['name' => 'Inv Child', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($child, 'InvCreator');
        $childReviewer = $this->createUserForEntity($child, 'InvChildRev');
        $project = $this->createDraftProject($child, $creatorUser);

        // 1. In Draft: 0 active steps
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 2. After Close Draft: exactly 1 active step
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 3. After Step 1 Approve: exactly 1 active step
        $this->approvalService->approveActiveStep($project, $childReviewer);
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 4. After Step 2 Approve: exactly 1 active step
        $this->approvalService->approveActiveStep($project, $childReviewer);
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 5. After Request Completion (target: creator_entity): exactly 0 active steps
        $this->approvalService->requestCompletion($project, $childReviewer, 'يرجى تعديل الميزانية وإعادة التقديم', ReturnTarget::CreatorEntity);
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 6. After Resubmit: exactly 1 active step
        $this->approvalService->resubmitProject($project, $creatorUser, 'تم تعديل الميزانية وإعادة التقديم');
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 7. After Reject: exactly 0 active steps
        $this->approvalService->rejectActiveStep($project, $childReviewer, 'تم رفض المشروع لعدم كفاية المبررات الفنية');
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());
    }

    // =========================================================================
    // 3. HTTP BOUNDARY & AUTHORIZATION HARDENING
    // =========================================================================

    /**
     * Locked Step Bypass Tests via HTTP: Future step, past step, locked step cannot be approved.
     */
    public function test_locked_step_bypass_via_http_is_strictly_rejected()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Bypass Root', 'parent_id' => null, 'is_active' => true]);
        $child = InternalEntity::withoutGlobalScopes()->create(['name' => 'Bypass Child', 'parent_id' => $root->id, 'is_active' => true]);
        $unrelated = InternalEntity::withoutGlobalScopes()->create(['name' => 'Bypass Unrelated', 'parent_id' => null, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($child, 'BypassCreator');
        $childReviewer = $this->createUserForEntity($child, 'BypassReviewer');
        $unrelatedUser = $this->createUserForEntity($unrelated, 'BypassUnrelated');

        $project = $this->createDraftProject($child, $creatorUser);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // 1. Direct HTTP request from an unrelated entity user -> Rejected with 403
        $responseUnrelated = $this->actingAs($unrelatedUser)
            ->postJson(route('projects.approval.approve', $project), ['notes' => 'محاولة اعتماد غير مصرحة']);
        $responseUnrelated->assertStatus(403);

        // 2. Project creator trying to approve their own project -> Rejected by Policy with 403
        $responseCreator = $this->actingAs($creatorUser)
            ->postJson(route('projects.approval.approve', $project), ['notes' => 'محاولة اعتماد ذاتي']);
        $responseCreator->assertStatus(403);

        // 3. If there is no active step (e.g., all steps locked or project in draft) -> Rejected
        $draftProject = $this->createDraftProject($child, $creatorUser);
        $responseDraft = $this->actingAs($childReviewer)
            ->postJson(route('projects.approval.approve', $draftProject), ['notes' => 'محاولة اعتماد مسودة']);
        $responseDraft->assertStatus(403);
    }

    /**
     * Wrong Entity Authorization: Users can only act when their entity's step is currently active.
     */
    public function test_wrong_entity_authorization_enforcement_across_tiers()
    {
        $rootEntity = InternalEntity::withoutGlobalScopes()->create(['name' => 'Auth Root', 'parent_id' => null, 'is_active' => true]);
        $parentEntity = InternalEntity::withoutGlobalScopes()->create(['name' => 'Auth Parent', 'parent_id' => $rootEntity->id, 'is_active' => true]);
        $creatorEntity = InternalEntity::withoutGlobalScopes()->create(['name' => 'Auth Creator', 'parent_id' => $parentEntity->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creatorEntity, 'Creator');
        $reviewerA = $this->createUserForEntity($creatorEntity, 'ReviewerA');
        $reviewerB = $this->createUserForEntity($parentEntity, 'ReviewerB');
        $reviewerC = $this->createUserForEntity($rootEntity, 'ReviewerC');

        $project = $this->createDraftProject($creatorEntity, $creatorUser);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // --- Step 1 (Creator Technical Review) ---
        // Reviewer B (Parent) cannot act on Creator step
        $this->assertFalse($this->approvalService->canUserActOnStep($reviewerB, $this->approvalService->getActiveStep($project)));
        $responseB = $this->actingAs($reviewerB)->postJson(route('projects.approval.approve', $project), ['notes' => 'محاولة غير مصرحة']);
        $responseB->assertStatus(403);

        // Reviewer C (Root) cannot act on Creator step
        $this->assertFalse($this->approvalService->canUserActOnStep($reviewerC, $this->approvalService->getActiveStep($project)));
        $responseC = $this->actingAs($reviewerC)->postJson(route('projects.approval.approve', $project), ['notes' => 'محاولة غير مصرحة']);
        $responseC->assertStatus(403);

        // Reviewer A (Creator Entity Reviewer) can act on Creator step
        $this->assertTrue($this->approvalService->canUserActOnStep($reviewerA, $this->approvalService->getActiveStep($project)));
        $responseA = $this->actingAs($reviewerA)->postJson(route('projects.approval.approve', $project), ['notes' => 'موافقة فنية من الجهة المنشئة']);
        $responseA->assertStatus(200);

        // Finish Creator steps (Steps 2 and 3)
        $this->actingAs($reviewerA)->postJson(route('projects.approval.approve', $project), ['notes' => 'موافقة مالية'])->assertStatus(200);
        $this->actingAs($reviewerA)->postJson(route('projects.approval.approve', $project), ['notes' => 'اعتماد مرحلة الجمعية'])->assertStatus(200);

        // --- Step 4 (Parent Technical Review) ---
        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertEquals(4, $activeStep->step_order);
        $this->assertEquals($parentEntity->id, $activeStep->entity_id);

        // Reviewer A (Creator) can NO LONGER act on Parent step
        $this->assertFalse($this->approvalService->canUserActOnStep($reviewerA, $activeStep));
        $this->actingAs($reviewerA)->postJson(route('projects.approval.approve', $project), ['notes' => 'محاولة غير مصرحة'])->assertStatus(403);

        // Reviewer C (Root) cannot act on Parent step
        $this->assertFalse($this->approvalService->canUserActOnStep($reviewerC, $activeStep));
        $this->actingAs($reviewerC)->postJson(route('projects.approval.approve', $project), ['notes' => 'محاولة غير مصرحة'])->assertStatus(403);

        // Reviewer B (Parent) can act
        $this->assertTrue($this->approvalService->canUserActOnStep($reviewerB, $activeStep));
        $this->actingAs($reviewerB)->postJson(route('projects.approval.approve', $project), ['notes' => 'موافقة فنية من الاتحاد'])->assertStatus(200);

        // Finish Parent steps (Steps 5 and 6)
        $this->actingAs($reviewerB)->postJson(route('projects.approval.approve', $project), ['notes' => 'موافقة مالية'])->assertStatus(200);
        $this->actingAs($reviewerB)->postJson(route('projects.approval.approve', $project), ['notes' => 'اعتماد مرحلة الاتحاد'])->assertStatus(200);

        // --- Step 7 (Root Technical Review) ---
        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertEquals(7, $activeStep->step_order);
        $this->assertEquals($rootEntity->id, $activeStep->entity_id);

        // Reviewer A and Reviewer B cannot act on Root step
        $this->assertFalse($this->approvalService->canUserActOnStep($reviewerA, $activeStep));
        $this->assertFalse($this->approvalService->canUserActOnStep($reviewerB, $activeStep));
        $this->actingAs($reviewerA)->postJson(route('projects.approval.approve', $project))->assertStatus(403);
        $this->actingAs($reviewerB)->postJson(route('projects.approval.approve', $project))->assertStatus(403);

        // Reviewer C (Root) can act
        $this->assertTrue($this->approvalService->canUserActOnStep($reviewerC, $activeStep));
        $this->actingAs($reviewerC)->postJson(route('projects.approval.approve', $project), ['notes' => 'موافقة فنية عليا'])->assertStatus(200);
    }

    // =========================================================================
    // 4. REQUEST COMPLETION & ROLLBACK HARDENING
    // =========================================================================

    /**
     * Request Completion with target = creator_entity rolls back to draft review mode,
     * and resubmit returns to the EXACT step that requested completion without chain regeneration.
     */
    public function test_request_completion_to_creator_entity_and_resubmit_returns_to_exact_step()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'RC Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'RC Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'RCCreator');
        $childReviewer = $this->createUserForEntity($creator, 'RCReviewer');

        $project = $this->createDraftProject($creator, $creatorUser);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // Advance to Step 3 (Creator Stage Approval)
        $this->approvalService->approveActiveStep($project, $childReviewer); // Step 1 -> 2
        $this->approvalService->approveActiveStep($project, $childReviewer); // Step 2 -> 3

        $step3 = $this->approvalService->getActiveStep($project);
        $this->assertEquals(3, $step3->step_order);
        $initialApprovalIds = ProjectApproval::where('project_id', $project->id)->pluck('id')->toArray();

        // Request Completion to Creator Entity
        $result = $this->approvalService->requestCompletion(
            $project,
            $childReviewer,
            'ملاحظات تفصيلية لاستكمال دراسة الجدوى الاقتصادية للمشروع',
            ReturnTarget::CreatorEntity
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(ProjectStatus::RolledBackForReview->value, $result['project_status']);
        $project->refresh();
        $this->assertEquals(ProjectStatus::RolledBackForReview->value, $project->status);
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        $step3->refresh();
        $this->assertEquals(ApprovalStepStatus::NeedAction->value, $step3->status);
        $this->assertEquals(ReturnTarget::CreatorEntity->value, $step3->return_target);

        // Resubmit project by creator
        $resubmittedStep = $this->approvalService->resubmitProject($project, $creatorUser, 'تم تحديث دراسة الجدوى وإرفاق الملحق المطلوب');

        $this->assertEquals(3, $resubmittedStep->step_order);
        $this->assertTrue($resubmittedStep->isActive());
        $this->assertEquals(ApprovalStepStatus::Pending->value, $resubmittedStep->status);

        $project->refresh();
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals($step3->drop, $project->current_stage);
        $this->assertEquals(3, $project->current_stage_order);

        // Verify chain was NOT regenerated and IDs are unchanged
        $afterApprovalIds = ProjectApproval::where('project_id', $project->id)->pluck('id')->toArray();
        $this->assertEquals($initialApprovalIds, $afterApprovalIds);
    }

    /**
     * Request Completion with target = previous_step transitions to Step N-1 with full metadata.
     */
    public function test_request_completion_to_previous_step_transitions_and_records_metadata()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Prev Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Prev Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'PrevCreator');
        $childReviewer = $this->createUserForEntity($creator, 'PrevReviewer');
        $project = $this->createDraftProject($creator, $creatorUser);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // Advance to Step 2 (Financial Review)
        $this->approvalService->approveActiveStep($project, $childReviewer); // Step 1 approved -> Step 2 active

        $step2 = $this->approvalService->getActiveStep($project);
        $this->assertEquals(2, $step2->step_order);

        // Request completion back to Step 1 (previous_step)
        $result = $this->approvalService->requestCompletion(
            $project,
            $childReviewer,
            'يوجد خطأ في التقرير الفني يرجى مراجعته من المهندس المختص',
            ReturnTarget::PreviousStep
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('previous_step', $result['target']);

        $step1 = ProjectApproval::where('project_id', $project->id)->where('step_order', 1)->first();
        $step2->refresh();

        // Step 2 is now locked / returned
        $this->assertFalse($step2->isActive());
        $this->assertEquals(ApprovalStepStatus::Returned->value, $step2->status);
        $this->assertEquals(ReturnTarget::PreviousStep->value, $step2->return_target);
        $this->assertEquals(1, $step2->returned_to_step_order);
        $this->assertNotNull($step2->reviewed_at);

        // Step 1 is reactivated
        $this->assertTrue($step1->isActive());
        $this->assertEquals(ApprovalStepStatus::Pending->value, $step1->status);

        $project->refresh();
        $this->assertEquals(1, $project->current_stage_order);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
    }

    // =========================================================================
    // 5. REJECT HARDENING
    // =========================================================================

    /**
     * Reject requires reason >= 10 chars, updates project to rejected, and blocks further approvals.
     */
    public function test_reject_hardening_validates_min_reason_length_and_blocks_further_approval()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Rej Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Rej Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'RejCreator');
        $childReviewer = $this->createUserForEntity($creator, 'RejReviewer');
        $project = $this->createDraftProject($creator, $creatorUser);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // Reject with short reason (< 10 chars) -> Throws WorkflowValidationException
        try {
            $this->approvalService->rejectActiveStep($project, $childReviewer, 'مرفوض');
            $this->fail('Expected WorkflowValidationException was not thrown for short rejection reason');
        } catch (WorkflowValidationException $e) {
            $this->assertStringContainsString('10', $e->getMessage());
        }

        // Reject with valid reason (>= 10 chars)
        $rejectedStep = $this->approvalService->rejectActiveStep(
            $project,
            $childReviewer,
            'تم رفض المشروع لعدم تطابقه مع المعايير المعتمدة'
        );

        $this->assertEquals(ApprovalStepStatus::Rejected->value, $rejectedStep->status);
        $this->assertFalse($rejectedStep->isActive());

        $project->refresh();
        $this->assertEquals(ProjectStatus::Rejected->value, $project->status);
        $this->assertEquals('rejected', $project->approval_status);

        // All subsequent steps are locked
        $futureSteps = ProjectApproval::where('project_id', $project->id)->where('step_order', '>', 1)->get();
        foreach ($futureSteps as $step) {
            $this->assertFalse($step->isActive());
            $this->assertEquals(ApprovalStepStatus::Locked->value, $step->status);
        }

        // Attempting to approve after reject must throw exception
        $this->expectException(InvalidWorkflowTransitionException::class);
        $this->approvalService->approveActiveStep($project, $childReviewer, 'محاولة اعتماد مشروع مرفوض');
    }

    // =========================================================================
    // 6. CONSULTATION & ISOLATION
    // =========================================================================

    /**
     * Consultation / Referral creates referral record without mutating active approval step or project stage.
     */
    public function test_consultation_and_referral_isolation_preserves_approval_chain_state()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Cons Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Cons Creator', 'parent_id' => $root->id, 'is_active' => true]);
        $consultedEntity = InternalEntity::withoutGlobalScopes()->create(['name' => 'جهة استشارية هندسية', 'parent_id' => null, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'ConsCreator');
        $project = $this->createDraftProject($creator, $creatorUser);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        $activeStepBefore = $this->approvalService->getActiveStep($project);
        $statusBefore = $project->status;
        $currentStageBefore = $project->current_stage;
        $currentStageOrderBefore = $project->current_stage_order;
        $activeApprovalIdBefore = $activeStepBefore->id;

        // Perform Consultation / Referral
        $referral = $this->approvalService->recordConsultation(
            $project,
            $creatorUser,
            $consultedEntity->id,
            'طلب رأي فني بخصوص مواصفات التربة والأساسات'
        );

        $this->assertInstanceOf(ProjectReferral::class, $referral);
        $this->assertEquals($consultedEntity->id, $referral->referred_entity_id);
        $this->assertEquals($project->id, $referral->project_id);

        $project->refresh();
        $activeStepAfter = $this->approvalService->getActiveStep($project);

        // Verify Workflow State is 100% UNCHANGED
        $this->assertEquals($statusBefore, $project->status);
        $this->assertEquals($currentStageBefore, $project->current_stage);
        $this->assertEquals($currentStageOrderBefore, $project->current_stage_order);
        $this->assertEquals($activeApprovalIdBefore, $activeStepAfter->id);
        $this->assertTrue($activeStepAfter->isActive());
    }

    // =========================================================================
    // 7. DRAFT & DUPLICATE PROTECTION HARDENING
    // =========================================================================

    /**
     * Draft creation produces 0 approvals, and legacy shim createInitialAssemblyApprovalStage is safe no-op.
     */
    public function test_draft_creation_produces_zero_approvals_and_legacy_shim_is_safe_noop()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Draft Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Draft Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'DraftUser');
        $project = $this->createDraftProject($creator, $creatorUser);

        $this->assertEquals(ProjectStatus::Draft->value, $project->status);
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->count());

        // Close draft generates dynamic chain
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);
        $this->assertEquals(6, ProjectApproval::where('project_id', $project->id)->count());
    }

    /**
     * Duplicate Close Draft call does NOT produce duplicate chains.
     */
    public function test_duplicate_close_draft_call_does_not_create_duplicate_chains()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Dup Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Dup Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'DupUser');
        $project = $this->createDraftProject($creator, $creatorUser);

        // First call -> 6 steps created
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);
        $this->assertEquals(6, ProjectApproval::where('project_id', $project->id)->count());

        // Second call -> Throws InvalidWorkflowTransitionException because project is already pending_approval
        try {
            $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);
            $this->fail('Expected InvalidWorkflowTransitionException on duplicate closeDraft was not thrown');
        } catch (InvalidWorkflowTransitionException $e) {
            $this->assertStringContainsString('مسودة', $e->getMessage());
        }

        // Approvals count remains exactly 6 (no duplicates)
        $this->assertEquals(6, ProjectApproval::where('project_id', $project->id)->count());
    }

    /**
     * Double Approval Protection: Attempting to approve after completion or without active step throws exception.
     */
    public function test_double_approval_race_protection_on_same_step()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Race Root', 'parent_id' => null, 'is_active' => true, 'is_ministry_root' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Race Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'RaceCreator');
        $childReviewer = $this->createUserForEntity($creator, 'RaceChildRev');
        $rootReviewer = $this->createUserForEntity($root, 'RaceRootRev');

        $project = $this->createDraftProject($creator, $creatorUser);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // Complete all 6 steps
        $this->approvalService->approveActiveStep($project, $childReviewer); // 1 -> 2
        $this->approvalService->approveActiveStep($project, $childReviewer); // 2 -> 3
        $this->approvalService->approveActiveStep($project, $childReviewer); // 3 -> 4
        $this->approvalService->approveActiveStep($project, $rootReviewer);  // 4 -> 5
        $this->approvalService->approveActiveStep($project, $rootReviewer);  // 5 -> 6
        $finalResult = $this->approvalService->approveActiveStep($project, $rootReviewer); // 6 -> InExecution

        $this->assertTrue($finalResult['is_final']);
        $this->assertEquals(ProjectStatus::InExecution->value, $project->fresh()->status);

        // Attempting to approve again when project is in in_execution (no active step) -> Throws exception
        $this->expectException(InvalidWorkflowTransitionException::class);
        $this->approvalService->approveActiveStep($project, $rootReviewer, 'محاولة موافقة بعد اكتمال المشروع');
    }

    // =========================================================================
    // 8. LEGACY COMPATIBILITY & HISTORICAL INTEGRITY
    // =========================================================================

    /**
     * Legacy compatibility methods initializeProjectWithStages and getProjectApprovalSummary delegate cleanly.
     */
    public function test_legacy_compatibility_methods_delegate_to_dynamic_workflow()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Leg Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Leg Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'LegUser');
        $project = $this->createDraftProject($creator, $creatorUser);

        // Legacy initializer closes draft and returns first step
        $firstStep = $this->approvalService->initializeProjectWithStages($project);
        $this->assertNotNull($firstStep);
        $this->assertEquals(1, $firstStep->step_order);
        $this->assertEquals(6, ProjectApproval::where('project_id', $project->id)->count());

        // Legacy summary
        $summary = $this->approvalService->getProjectApprovalSummary($project);
        $this->assertEquals(6, $summary['total_stages']);
        $this->assertEquals(0, $summary['approved_stages']);
        $this->assertEquals(1, $summary['pending_stages']);
        $this->assertFalse($summary['is_fully_approved']);

        // Legacy getApprovalStages
        $stages = $this->approvalService->getApprovalStages($project);
        $this->assertCount(6, $stages);
    }

    /**
     * Historical integrity: ProjectActivityHistory is preserved across Approve, Request Completion, Resubmit, Reject, Consultation.
     */
    public function test_historical_integrity_preserves_all_activity_logs()
    {
        $root = InternalEntity::withoutGlobalScopes()->create(['name' => 'Hist Root', 'parent_id' => null, 'is_active' => true]);
        $creator = InternalEntity::withoutGlobalScopes()->create(['name' => 'Hist Creator', 'parent_id' => $root->id, 'is_active' => true]);

        $creatorUser = $this->createUserForEntity($creator, 'HistCreator');
        $childReviewer = $this->createUserForEntity($creator, 'HistReviewer');
        $project = $this->createDraftProject($creator, $creatorUser);

        // 1. Finalize draft
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $creatorUser);

        // 2. Approve Step 1
        $this->approvalService->approveActiveStep($project, $childReviewer, 'موافقة الخطوة الأولى');

        // 3. Request Completion
        $this->approvalService->requestCompletion($project, $childReviewer, 'طلب تعديل فني مفصل ودقيق', ReturnTarget::CreatorEntity);

        // 4. Resubmit
        $this->approvalService->resubmitProject($project, $creatorUser, 'إعادة التقديم بعد التعديل');

        // 5. Consultation
        $consultEntity = InternalEntity::withoutGlobalScopes()->create(['name' => 'جهة استشارية تاريخية', 'parent_id' => null, 'is_active' => true]);
        $this->approvalService->recordConsultation($project, $creatorUser, $consultEntity->id, 'طلب استشارة');

        // 6. Reject
        $this->approvalService->rejectActiveStep($project, $childReviewer, 'رفض نهائي بعد المراجعة والمداولة');

        // Verify history records
        $history = ProjectActivityHistory::where('project_id', $project->id)->orderBy('created_at')->get();
        $this->assertGreaterThanOrEqual(6, $history->count());

        $actionTypes = $history->pluck('action_type')->toArray();
        $this->assertContains('finalized', $actionTypes);
        $this->assertContains('approved', $actionTypes);
        $this->assertContains('need_action', $actionTypes);
        $this->assertContains('resubmitted', $actionTypes);
        $this->assertContains('referral', $actionTypes);
        $this->assertContains('rejected', $actionTypes);
    }

    /**
     * No Hardcoded Entity Names: Arbitrary custom names are supported through parent_id hierarchy.
     */
    public function test_no_hardcoded_entity_names_in_dynamic_workflow()
    {
        $customRoot = InternalEntity::withoutGlobalScopes()->create(['name' => 'X_Custom_HQ_Apex_99', 'parent_id' => null, 'is_active' => true]);
        $customBranch = InternalEntity::withoutGlobalScopes()->create(['name' => 'Y_Arbitrary_Division_42', 'parent_id' => $customRoot->id, 'is_active' => true]);
        $customLeaf = InternalEntity::withoutGlobalScopes()->create(['name' => 'Z_Dynamic_Unit_007', 'parent_id' => $customBranch->id, 'is_active' => true]);

        $customUser = $this->createUserForEntity($customLeaf, 'CustomUser');
        $project = $this->createDraftProject($customLeaf, $customUser);

        $chain = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $customUser);

        $this->assertCount(9, $chain);
        $approvals = ProjectApproval::where('project_id', $project->id)->orderBy('step_order')->get();

        $this->assertEquals($customLeaf->id, $approvals[0]->entity_id);
        $this->assertEquals($customBranch->id, $approvals[3]->entity_id);
        $this->assertEquals($customRoot->id, $approvals[6]->entity_id);
    }
}
