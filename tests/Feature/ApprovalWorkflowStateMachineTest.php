<?php

namespace Tests\Feature;

use App\Enums\ApprovalPhase;
use App\Enums\ApprovalStepStatus;
use App\Enums\EntityResponsibilityType;
use App\Enums\ProjectStatus;
use App\Enums\ReturnTarget;
use App\Exceptions\InvalidWorkflowTransitionException;
use App\Exceptions\UnauthorizedWorkflowActionException;
use App\Exceptions\WorkflowValidationException;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApprovalWorkflowStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected InternalEntity $childEntity;

    protected InternalEntity $parentEntity;

    protected User $creatorUser;

    protected User $childReviewer;

    protected User $parentReviewer;

    protected User $outsiderUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalService = app(ApprovalService::class);

        // Setup 2-tier Entity hierarchy: Parent Entity -> Child Entity
        $this->parentEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الاتحاد العام / الجهة الأب (Root Entity)',
            'is_active' => true,
            'is_ministry_root' => true,
        ]);

        $this->childEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجمعية الفرعية / الجهة المنشئة (Child Entity)',
            'parent_id' => $this->parentEntity->id,
            'is_active' => true,
        ]);

        // Users
        $this->creatorUser = User::withoutGlobalScopes()->create([
            'name' => 'منشئ المشروع',
            'username' => 'creator_test_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'creator_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->childEntity->id,
            'status' => 'Active',
            'organization_type' => 'internal',
        ]);

        $this->childReviewer = User::withoutGlobalScopes()->create([
            'name' => 'مراجع الجهة الفرعية',
            'username' => 'child_rev_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'child_rev_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->childEntity->id,
            'status' => 'Active',
            'organization_type' => 'internal',
        ]);

        $this->parentReviewer = User::withoutGlobalScopes()->create([
            'name' => 'مراجع الجهة الأب',
            'username' => 'parent_rev_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'parent_rev_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->parentEntity->id,
            'status' => 'Active',
            'organization_type' => 'internal',
        ]);

        $unrelatedEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'جهة خارجية غير مرتبطة بالسلسلة',
            'is_active' => true,
        ]);

        $this->outsiderUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم خارجي غير مصرح',
            'username' => 'outsider_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'outsider_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $unrelatedEntity->id,
            'status' => 'Active',
            'organization_type' => 'internal',
        ]);

        // Entity Approval Stages Configuration
        foreach ([
            EntityResponsibilityType::TechnicalReview,
            EntityResponsibilityType::FinancialReview,
            EntityResponsibilityType::Approval,
        ] as $type) {
            EntityApprovalStage::create([
                'entity_id' => $this->childEntity->id,
                'stage' => $type->value,
                'stage_order' => $type->stageOrder(),
                'responsible_user_id' => $this->childReviewer->id,
            ]);

            EntityApprovalStage::create([
                'entity_id' => $this->parentEntity->id,
                'stage' => $type->value,
                'stage_order' => $type->stageOrder(),
                'responsible_user_id' => $this->parentReviewer->id,
            ]);
        }
    }

    protected function createDraftProject(): Project
    {
        return Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع اختباري لسير الموافقات',
            'status' => ProjectStatus::Draft->value,
            'creator_entity_id' => $this->childEntity->id,
            'internal_entity_id' => $this->childEntity->id,
            'created_by_user_id' => $this->creatorUser->id,
            'created_by' => $this->creatorUser->id,
        ]);
    }

    /**
     * Scenario A: Draft -> Close Draft -> Entity 1 Technical (Active, others Locked)
     */
    public function test_scenario_a_draft_to_close_draft_activates_only_first_step()
    {
        $project = $this->createDraftProject();

        // Ensure initially 0 approval steps exist for draft
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->count());
        $this->assertTrue($project->isDraft());

        // Close draft
        $steps = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // For 2 entities, we must have exactly 6 steps (3 per entity)
        $this->assertCount(6, $steps);
        $project->refresh();

        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals('entity_'.$this->childEntity->id.'_technical_review', $project->current_stage);
        $this->assertEquals(1, $project->current_stage_order);

        // Verify Step 1 is ACTIVE and PENDING
        $step1 = $steps->firstWhere('step_order', 1);
        $this->assertTrue($step1->isActive());
        $this->assertEquals(ApprovalStepStatus::Pending->value, $step1->status);
        $this->assertEquals(ApprovalPhase::TechnicalReview->value, $step1->phase);
        $this->assertEquals($this->childEntity->id, $step1->entity_id);

        // Verify all subsequent steps (2 to 6) are LOCKED and NOT active
        $remainingSteps = $steps->where('step_order', '>', 1);
        $this->assertCount(5, $remainingSteps);
        foreach ($remainingSteps as $step) {
            $this->assertFalse($step->isActive());
            $this->assertEquals(ApprovalStepStatus::Locked->value, $step->status);
        }
    }

    /**
     * Scenario B: Technical -> Financial -> Stage Approval (Within Entity 1)
     */
    public function test_scenario_b_sub_stages_progression_within_entity()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Step 1: Approve Technical Review
        $res1 = $this->approvalService->approveActiveStep($project, $this->childReviewer, 'موافقة فنية معتمدة');
        $this->assertTrue($res1['success']);
        $this->assertFalse($res1['is_final']);

        $project->refresh();
        $this->assertEquals('entity_'.$this->childEntity->id.'_financial_review', $project->current_stage);
        $this->assertEquals(2, $project->current_stage_order);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($activeStep);
        $this->assertEquals(2, $activeStep->step_order);
        $this->assertEquals(ApprovalPhase::FinancialReview->value, $activeStep->phase);

        // Step 2: Approve Financial Review
        $res2 = $this->approvalService->approveActiveStep($project, $this->childReviewer, 'موافقة مالية معتمدة');
        $this->assertTrue($res2['success']);

        $project->refresh();
        $this->assertEquals('entity_'.$this->childEntity->id.'_stage_approval', $project->current_stage);
        $this->assertEquals(3, $project->current_stage_order);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertEquals(3, $activeStep->step_order);
        $this->assertEquals(ApprovalPhase::StageApproval->value, $activeStep->phase);
    }

    /**
     * Scenario C: Stage Approval -> Next Entity Technical
     */
    public function test_scenario_c_progression_from_entity1_to_entity2()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Complete Entity 1 steps (1, 2, 3)
        $this->approvalService->approveActiveStep($project, $this->childReviewer);
        $this->approvalService->approveActiveStep($project, $this->childReviewer);
        $res = $this->approvalService->approveActiveStep($project, $this->childReviewer, 'اعتماد مسؤول الجهة الفرعية');

        $this->assertTrue($res['success']);
        $project->refresh();

        // Project moves to Entity 2 Technical Review (step 4)
        $this->assertEquals('entity_'.$this->parentEntity->id.'_technical_review', $project->current_stage);
        $this->assertEquals(4, $project->current_stage_order);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertEquals(4, $activeStep->step_order);
        $this->assertEquals($this->parentEntity->id, $activeStep->entity_id);
        $this->assertEquals(ApprovalPhase::TechnicalReview->value, $activeStep->phase);
    }

    /**
     * Scenario D: Root Stage Approval -> in_execution (Final Approval)
     */
    public function test_scenario_d_root_stage_approval_moves_to_in_execution()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Steps 1, 2, 3 (Child Entity)
        $this->approvalService->approveActiveStep($project, $this->childReviewer);
        $this->approvalService->approveActiveStep($project, $this->childReviewer);
        $this->approvalService->approveActiveStep($project, $this->childReviewer);

        // Steps 4, 5 (Parent Entity Technical & Financial)
        $this->approvalService->approveActiveStep($project, $this->parentReviewer);
        $this->approvalService->approveActiveStep($project, $this->parentReviewer);

        // Step 6: Parent Root Stage Approval -> FINAL
        $resFinal = $this->approvalService->approveActiveStep($project, $this->parentReviewer, 'الاعتماد النهائي للمشروع');

        $this->assertTrue($resFinal['success']);
        $this->assertTrue($resFinal['is_final']);

        $project->refresh();
        $this->assertEquals(ProjectStatus::InExecution->value, $project->status);
        $this->assertNull($project->current_stage);
        $this->assertNotNull($project->completed_at);
    }

    /**
     * Scenario E: Request Completion -> CreatorEntity -> Resubmit -> returns to same Active Step
     */
    public function test_scenario_e_request_completion_to_creator_and_resubmit()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Move to Step 2 (Financial)
        $this->approvalService->approveActiveStep($project, $this->childReviewer);

        // Step 2 Reviewer requests completion from Creator Entity
        $reqRes = $this->approvalService->requestCompletion(
            $project,
            $this->childReviewer,
            'يرجى إرفاق عروض الأسعار المحدثة والتكلفة التفصيلية',
            ReturnTarget::CreatorEntity
        );

        $this->assertTrue($reqRes['success']);
        $project->refresh();
        $this->assertEquals(ProjectStatus::RolledBackForReview->value, $project->status);

        // Creator modifies and resubmits
        $resubmittedStep = $this->approvalService->resubmitProject($project, $this->creatorUser, 'تم إرفاق العروض المطلوبة بنجاح');

        $project->refresh();
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals($resubmittedStep->drop, $project->current_stage);
        $this->assertEquals(2, $project->current_stage_order);
        $this->assertTrue($resubmittedStep->isActive());
    }

    /**
     * Scenario F: Request Completion -> PreviousStep -> Previous Step becomes Active
     */
    public function test_scenario_f_request_completion_to_previous_step()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Complete Step 1 (Technical) -> Active is Step 2 (Financial)
        $this->approvalService->approveActiveStep($project, $this->childReviewer);

        // Step 2 returns to Step 1 (Technical)
        $reqRes = $this->approvalService->requestCompletion(
            $project,
            $this->childReviewer,
            'يرجى مراجعة المواصفات الفنية للبنود الهندسية',
            ReturnTarget::PreviousStep
        );

        $this->assertTrue($reqRes['success']);
        $this->assertEquals('previous_step', $reqRes['target']);

        $project->refresh();
        $this->assertEquals(1, $project->current_stage_order);

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertEquals(1, $activeStep->step_order);
        $this->assertTrue($activeStep->isActive());
    }

    /**
     * Scenario G: Action on Locked Step must fail
     */
    public function test_scenario_g_action_on_locked_step_fails()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Active step is 1. If we try to call approve when no active step exists or bypass:
        $step5 = ProjectApproval::where('project_id', $project->id)->where('step_order', 5)->first();
        $this->assertTrue($step5->isLocked());
        $this->assertFalse($step5->isActive());

        // Deactivate all steps to simulate no active step
        ProjectApproval::where('project_id', $project->id)->update(['is_active' => false]);

        $this->expectException(InvalidWorkflowTransitionException::class);
        $this->approvalService->approveActiveStep($project, $this->parentReviewer);
    }

    /**
     * Scenario H: Action from user outside the step's Entity must fail
     */
    public function test_scenario_h_action_from_outside_entity_fails()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Step 1 belongs to childEntity. outsiderUser is entity 999999
        $this->expectException(UnauthorizedWorkflowActionException::class);
        $this->approvalService->approveActiveStep($project, $this->outsiderUser);
    }

    /**
     * Scenario I: Reject without reason must fail
     */
    public function test_scenario_i_reject_without_reason_fails()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        $this->expectException(WorkflowValidationException::class);
        // Reason less than 10 characters
        $this->approvalService->rejectActiveStep($project, $this->childReviewer, 'مرفوض');
    }

    /**
     * Scenario J: Consultation does NOT change active step
     */
    public function test_scenario_j_consultation_does_not_change_active_step()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        $project->refresh();
        $initialStage = $project->current_stage;
        $initialOrder = $project->current_stage_order;
        $initialActiveStepId = $this->approvalService->getActiveStep($project)->id;

        // Record a consultation to parent entity
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->creatorUser,
            $this->parentEntity->id,
            'نرجو التكرم بإبداء الرأي الاستشاري حول دراسة الجدوى للمشروع.'
        );

        $this->assertNotNull($referral);
        $this->assertEquals('pending', $referral->status);

        $project->refresh();
        // Verify current active step remains exactly the same
        $this->assertEquals($initialStage, $project->current_stage);
        $this->assertEquals($initialOrder, $project->current_stage_order);
        $this->assertEquals($initialActiveStepId, $this->approvalService->getActiveStep($project)->id);
    }

    /**
     * Test Invariant: Exactly ZERO or ONE active step at any point in the workflow
     */
    public function test_invariant_only_single_active_step_at_all_lifecycle_stages()
    {
        $project = $this->createDraftProject();

        // 1. In Draft: 0 active steps
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 2. Closed Draft: exactly 1 active step
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 3. Step 1 -> Step 2: exactly 1 active step
        $this->approvalService->approveActiveStep($project, $this->childReviewer);
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 4. Request Completion to Creator: exactly 0 active steps (creator is in editing mode)
        $this->approvalService->requestCompletion(
            $project,
            $this->childReviewer,
            'ملاحظات استكمال للنواقص الفنية والمالية',
            ReturnTarget::CreatorEntity
        );
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 5. Resubmit by creator: exactly 1 active step
        $this->approvalService->resubmitProject($project, $this->creatorUser, 'تم الاستكمال والتصحيح');
        $this->assertEquals(1, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());

        // 6. Complete remaining steps to in_execution: exactly 0 active steps
        $this->approvalService->approveActiveStep($project, $this->childReviewer); // step 2
        $this->approvalService->approveActiveStep($project, $this->childReviewer); // step 3 (Child entity stage approval)
        $this->approvalService->approveActiveStep($project, $this->parentReviewer); // step 4 (Parent tech review)
        $this->approvalService->approveActiveStep($project, $this->parentReviewer); // step 5 (Parent fin review)
        $finalRes = $this->approvalService->approveActiveStep($project, $this->parentReviewer); // step 6 (Parent stage approval - Final)

        $this->assertTrue($finalRes['is_final']);
        $this->assertEquals(ProjectStatus::InExecution->value, $project->fresh()->status);
        $this->assertEquals(0, ProjectApproval::where('project_id', $project->id)->where('is_active', true)->count());
    }

    /**
     * Test Resubmit returns precisely to the step that requested action
     */
    public function test_resubmit_returns_to_exact_step_that_requested_completion()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Advance through steps 1, 2, 3 (Child Entity) to Step 4 (Parent Entity Tech Review)
        $this->approvalService->approveActiveStep($project, $this->childReviewer); // Step 1 approved -> Step 2 active
        $this->approvalService->approveActiveStep($project, $this->childReviewer); // Step 2 approved -> Step 3 active
        $this->approvalService->approveActiveStep($project, $this->childReviewer); // Step 3 approved -> Step 4 active

        $activeStep = $this->approvalService->getActiveStep($project);
        $this->assertEquals(4, $activeStep->step_order);
        $this->assertEquals($this->parentEntity->id, $activeStep->entity_id);

        // Parent Reviewer requests completion to CreatorEntity
        $this->approvalService->requestCompletion(
            $project,
            $this->parentReviewer,
            'نرجو تعديل بنود الموازنة لتتناسب مع اللائحة التنفيذية.',
            ReturnTarget::CreatorEntity
        );

        $project->refresh();
        $this->assertEquals(ProjectStatus::RolledBackForReview->value, $project->status);

        // Creator resubmits
        $this->approvalService->resubmitProject($project, $this->creatorUser, 'تم تعديل الموازنة بالكامل');

        $project->refresh();
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals(4, $project->current_stage_order);

        $reactivatedStep = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($reactivatedStep);
        $this->assertEquals(4, $reactivatedStep->step_order);
        $this->assertTrue($reactivatedStep->isActive());

        // Verify previous steps (1, 2, 3) remain approved and intact
        $this->assertEquals(ApprovalStepStatus::Approved->value, ProjectApproval::where('project_id', $project->id)->where('step_order', 1)->first()->status);
        $this->assertEquals(ApprovalStepStatus::Approved->value, ProjectApproval::where('project_id', $project->id)->where('step_order', 2)->first()->status);
        $this->assertEquals(ApprovalStepStatus::Approved->value, ProjectApproval::where('project_id', $project->id)->where('step_order', 3)->first()->status);
    }

    /**
     * Test legacy compatibility shims and drop column preservation
     */
    public function test_legacy_compatibility_shims_and_drop_column()
    {
        $project = $this->createDraftProject();
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->creatorUser);

        // Verify getApprovalStages returns stages collection
        $stages = $this->approvalService->getApprovalStages($project);
        $this->assertCount(6, $stages);

        // Verify drop values are present on all approvals
        $approvals = ProjectApproval::where('project_id', $project->id)->get();
        foreach ($approvals as $approval) {
            $this->assertNotEmpty($approval->drop);
            $this->assertStringStartsWith('entity_', $approval->drop);
        }

        // Verify initializeRecursiveApprovals shim works without error
        $project2 = $this->createDraftProject();
        $this->approvalService->initializeRecursiveApprovals($project2, $this->childEntity->id, $this->creatorUser);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project2->fresh()->status);
        $this->assertEquals(6, ProjectApproval::where('project_id', $project2->id)->count());
    }

    /**
     * Test database schema integrity
     */
    public function test_database_schema_integrity()
    {
        $columns = Schema::getColumnListing('project_approvals');
        $requiredColumns = [
            'id',
            'project_id',
            'entity_id',
            'drop',
            'phase',
            'step_order',
            'status',
            'is_active',
            'return_target',
            'returned_to_step_order',
            'notes',
            'attachment',
            'financial_review_status',
            'technical_review_status',
            'reviewed_by',
            'reviewed_at',
        ];

        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "Column '{$col}' must exist on project_approvals table.");
        }
    }
}
