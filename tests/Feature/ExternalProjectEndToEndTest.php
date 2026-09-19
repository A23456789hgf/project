<?php

namespace Tests\Feature;

use App\Enums\EntityResponsibilityType;
use App\Enums\ProjectStatus;
use App\Exceptions\ConfigurationException;
use App\Exceptions\UnauthorizedWorkflowActionException;
use App\Models\Authority;
use App\Models\AuthorityApprovalRoute;
use App\Models\AuthorityApprovalStage;
use App\Models\Directorate;
use App\Models\EntityApprovalStage;
use App\Models\Governorate;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalProjectEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected InternalEntity $ministry;

    protected Authority $authorityA;

    protected Authority $authorityB;

    protected Governorate $governorate;

    protected Directorate $directorate;

    protected User $externalCreator;

    protected User $authATech;

    protected User $authAFin;

    protected User $authAApp;

    protected User $authBUser;

    protected User $ministryTech;

    protected User $ministryFin;

    protected User $ministryApp;

    protected User $internalGovUser;

    protected ApprovalService $approvalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalService = app(ApprovalService::class);

        // 1. Geographic Setup
        $this->governorate = Governorate::forceCreate(['name' => 'صنعاء', 'is_active' => true]);
        $this->directorate = Directorate::forceCreate(['name' => 'السبعين', 'governorate_id' => $this->governorate->id, 'is_active' => true]);

        // 2. Ministry Root Entity
        $this->ministry = InternalEntity::forceCreate([
            'name' => 'وزارة الزراعة والري',
            'entity_type' => 'Company',
            'is_active' => true,
            'is_ministry_root' => true,
        ]);

        // 3. Authorities
        $this->authorityA = Authority::forceCreate([
            'agency_name' => 'هيئة تطوير تهامة',
            'is_active' => true,
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
        ]);

        $this->authorityB = Authority::forceCreate([
            'agency_name' => 'هيئة مياه الريف',
            'is_active' => true,
        ]);

        // 4. External Users
        $this->externalCreator = User::forceCreate([
            'user_id' => 'EXT_CR',
            'phone' => '771000001',
            'username' => 'ext_creator',
            'name' => 'مهندس المشاريع بالهيئة',
            'email' => 'ext_creator@tihama.gov.ye',
            'password' => 'password',
            'authority_id' => $this->authorityA->id,
            'organization_type' => 'external',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'status' => 'Active',
        ]);

        $this->authATech = User::forceCreate([
            'user_id' => 'EXT_T',
            'phone' => '771000002',
            'username' => 'autha_tech',
            'name' => 'المراجع الفني بالهيئة',
            'email' => 'tech@tihama.gov.ye',
            'password' => 'password',
            'authority_id' => $this->authorityA->id,
            'organization_type' => 'external',
            'responsibility' => 'TECHNICAL',
            'status' => 'Active',
        ]);

        $this->authAFin = User::forceCreate([
            'user_id' => 'EXT_F',
            'phone' => '771000003',
            'username' => 'autha_fin',
            'name' => 'المراجع المالي بالهيئة',
            'email' => 'fin@tihama.gov.ye',
            'password' => 'password',
            'authority_id' => $this->authorityA->id,
            'organization_type' => 'external',
            'responsibility' => 'FINANCIAL',
            'status' => 'Active',
        ]);

        $this->authAApp = User::forceCreate([
            'user_id' => 'EXT_A',
            'phone' => '771000004',
            'username' => 'autha_app',
            'name' => 'رئيس الهيئة / معتمد',
            'email' => 'president@tihama.gov.ye',
            'password' => 'password',
            'authority_id' => $this->authorityA->id,
            'organization_type' => 'external',
            'responsibility' => 'ENTITY_APPROVER',
            'status' => 'Active',
        ]);

        $this->authBUser = User::forceCreate([
            'user_id' => 'EXT_B',
            'phone' => '771000005',
            'username' => 'authb_user',
            'name' => 'مسؤول هيئة الريف',
            'email' => 'user@reef.gov.ye',
            'password' => 'password',
            'authority_id' => $this->authorityB->id,
            'organization_type' => 'external',
            'responsibility' => 'ENTITY_APPROVER',
            'status' => 'Active',
        ]);

        // 5. Ministry Users
        $this->ministryTech = User::forceCreate([
            'user_id' => 'MIN_T',
            'phone' => '771000010',
            'username' => 'min_tech',
            'name' => 'المراجع الفني بالوزارة',
            'email' => 'min_tech@moa.gov.ye',
            'password' => 'password',
            'entity_id' => $this->ministry->id,
            'organization_type' => 'internal',
            'responsibility' => 'TECHNICAL',
            'status' => 'Active',
        ]);

        $this->ministryFin = User::forceCreate([
            'user_id' => 'MIN_F',
            'phone' => '771000011',
            'username' => 'min_fin',
            'name' => 'المراجع المالي بالوزارة',
            'email' => 'min_fin@moa.gov.ye',
            'password' => 'password',
            'entity_id' => $this->ministry->id,
            'organization_type' => 'internal',
            'responsibility' => 'FINANCIAL',
            'status' => 'Active',
        ]);

        $this->ministryApp = User::forceCreate([
            'user_id' => 'MIN_A',
            'phone' => '771000012',
            'username' => 'min_app',
            'name' => 'معتمد الوزارة',
            'email' => 'min_app@moa.gov.ye',
            'password' => 'password',
            'entity_id' => $this->ministry->id,
            'organization_type' => 'internal',
            'responsibility' => 'ENTITY_APPROVER',
            'status' => 'Active',
        ]);

        // 6. Internal non-ministry user
        $subEntity = InternalEntity::forceCreate(['name' => 'مكتب الزراعة بالأمانة', 'is_active' => true, 'parent_id' => $this->ministry->id]);
        $this->internalGovUser = User::forceCreate([
            'user_id' => 'INT_G',
            'phone' => '771000020',
            'username' => 'int_gov',
            'name' => 'مهندس مكتب الأمانة',
            'email' => 'gov@moa.gov.ye',
            'password' => 'password',
            'entity_id' => $subEntity->id,
            'organization_type' => 'internal',
            'responsibility' => 'TECHNICAL',
            'status' => 'Active',
        ]);

        // 7. Stage Configurations
        // Authority A: Technical -> Financial -> Approval
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityA->id, 'stage' => EntityResponsibilityType::TechnicalReview->value, 'stage_order' => 1, 'is_active' => true, 'responsible_user_id' => $this->authATech->id]);
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityA->id, 'stage' => EntityResponsibilityType::FinancialReview->value, 'stage_order' => 2, 'is_active' => true, 'responsible_user_id' => $this->authAFin->id]);
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityA->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'is_active' => true, 'responsible_user_id' => $this->authAApp->id]);

        // Ministry: Technical -> Financial -> Approval
        EntityApprovalStage::forceCreate(['entity_id' => $this->ministry->id, 'stage' => EntityResponsibilityType::TechnicalReview->value, 'stage_order' => 1, 'is_active' => true, 'responsible_user_id' => $this->ministryTech->id]);
        EntityApprovalStage::forceCreate(['entity_id' => $this->ministry->id, 'stage' => EntityResponsibilityType::FinancialReview->value, 'stage_order' => 2, 'is_active' => true, 'responsible_user_id' => $this->ministryFin->id]);
        EntityApprovalStage::forceCreate(['entity_id' => $this->ministry->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'is_active' => true, 'responsible_user_id' => $this->ministryApp->id]);

        // Active route: Authority A -> Ministry
        AuthorityApprovalRoute::create([
            'authority_id' => $this->authorityA->id,
            'destination_type' => 'ministry',
            'is_active' => true,
        ]);
    }

    /**
     * 1. End-to-End External Lifecycle:
     * Creation -> Save -> Draft Closure -> Multi-tier Approval -> Final Execution.
     */
    public function test_external_project_complete_lifecycle_to_in_execution(): void
    {
        // Act as external creator
        $this->actingAs($this->externalCreator);

        $project = Project::create([
            'project_name' => 'مشروع حفر قنوات ري سهل تهامة',
            'created_by_user_id' => $this->externalCreator->id,
            'authority_id' => $this->externalCreator->authority_id,
            'source_type' => 'external',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'status' => 'draft',
            'last_saved_step' => 1,
        ]);

        // Verifications on creation
        $this->assertEquals('external', $project->source_type);
        $this->assertEquals($this->authorityA->id, $project->authority_id);
        $this->assertNull($project->creator_entity_id, 'External project must not have creator_entity_id');
        $this->assertNull($project->internal_entity_id, 'External project must not have internal_entity_id');
        $this->assertEquals(ProjectStatus::Draft->value, $project->status);
        $this->assertEquals($this->governorate->id, $this->externalCreator->governorate_id);
        $this->assertEquals($this->directorate->id, $this->externalCreator->directorate_id);

        // Close draft and generate approval chain
        $steps = $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->externalCreator);

        // Verification of Chain Generation
        $this->assertCount(6, $steps);
        $project->refresh();
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->status);
        $this->assertEquals(1, $project->current_stage_order);
        $this->assertEquals('authority_'.$this->authorityA->id.'_technical_review', $project->current_stage);

        // Step 1: External Auth A Technical Review
        $step1 = $this->approvalService->getActiveStep($project);
        $this->assertNotNull($step1);
        $this->assertEquals('technical_review', $step1->phase);
        $this->assertEquals('external', $step1->approver_scope);
        $this->assertEquals($this->authorityA->id, $step1->authority_id);
        $res1 = $this->approvalService->approveActiveStep($project, $this->authATech, 'تمت المراجعة الفنية بنجاح');
        $this->assertTrue($res1['success']);
        $this->assertFalse($res1['is_final']);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);

        // Step 2: External Auth A Financial Review
        $step2 = $this->approvalService->getActiveStep($project);
        $this->assertEquals('financial_review', $step2->phase);
        $res2 = $this->approvalService->approveActiveStep($project, $this->authAFin, 'الميزانية مطابقة');
        $this->assertTrue($res2['success']);
        $this->assertFalse($res2['is_final']);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);

        // Step 3: External Auth A Approval (Entity Head)
        $step3 = $this->approvalService->getActiveStep($project);
        $this->assertEquals('stage_approval', $step3->phase);
        $res3 = $this->approvalService->approveActiveStep($project, $this->authAApp, 'معتمد من رئيس الهيئة ومرفوع للوزارة');
        $this->assertTrue($res3['success']);
        $this->assertFalse($res3['is_final']);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);

        // Step 4: Ministry Technical Review
        $step4 = $this->approvalService->getActiveStep($project);
        $this->assertEquals('technical_review', $step4->phase);
        $this->assertEquals('internal', $step4->approver_scope);
        $this->assertEquals($this->ministry->id, $step4->entity_id);
        $res4 = $this->approvalService->approveActiveStep($project, $this->ministryTech, 'المواصفات الفنية مستوفاة للوزارة');
        $this->assertTrue($res4['success']);
        $this->assertFalse($res4['is_final']);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);

        // Step 5: Ministry Financial Review
        $step5 = $this->approvalService->getActiveStep($project);
        $this->assertEquals('financial_review', $step5->phase);
        $res5 = $this->approvalService->approveActiveStep($project, $this->ministryFin, 'الاعتماد المالي متوفر');
        $this->assertTrue($res5['success']);
        $this->assertFalse($res5['is_final']);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $project->fresh()->status);

        // Step 6: Ministry Final Approval
        $step6 = $this->approvalService->getActiveStep($project);
        $this->assertEquals('stage_approval', $step6->phase);
        $this->assertEquals('internal', $step6->approver_scope);
        $res6 = $this->approvalService->approveActiveStep($project, $this->ministryApp, 'اعتماد نهائي وإحالة للتنفيذ');
        $this->assertTrue($res6['success']);
        $this->assertTrue($res6['is_final']);

        // Final State Assertion: Project must be in_execution
        $project->refresh();
        $this->assertEquals(ProjectStatus::InExecution->value, $project->status);
        $this->assertEquals('approved', $project->approval_status);
        $this->assertNotNull($project->completed_at);
    }

    /**
     * 2. Security & Boundaries:
     * Cross-authority, cross-scope, and out-of-order execution prevention.
     */
    public function test_security_boundaries_and_unauthorized_actions(): void
    {
        $project = Project::create([
            'project_name' => 'مشروع أمني اختباري',
            'created_by_user_id' => $this->externalCreator->id,
            'authority_id' => $this->authorityA->id,
            'source_type' => 'external',
            'status' => 'draft',
            'last_saved_step' => 1,
        ]);

        // An outsider user from Authority B cannot close draft for Authority A
        $this->expectException(UnauthorizedWorkflowActionException::class);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->authBUser);
    }

    public function test_internal_user_cannot_approve_external_step(): void
    {
        $project = Project::create([
            'project_name' => 'مشروع اختبار عزل النطاق',
            'created_by_user_id' => $this->externalCreator->id,
            'authority_id' => $this->authorityA->id,
            'source_type' => 'external',
            'status' => 'draft',
            'last_saved_step' => 1,
        ]);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->externalCreator);

        // Step 1 belongs to Authority A (external). Ministry Technical user (internal) cannot approve it.
        $this->expectException(UnauthorizedWorkflowActionException::class);
        $this->approvalService->approveActiveStep($project, $this->ministryTech, 'محاولة غير مصرحة');
    }

    public function test_outsider_authority_user_cannot_approve_step(): void
    {
        $project = Project::create([
            'project_name' => 'مشروع عزل الهيئات',
            'created_by_user_id' => $this->externalCreator->id,
            'authority_id' => $this->authorityA->id,
            'source_type' => 'external',
            'status' => 'draft',
            'last_saved_step' => 1,
        ]);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->externalCreator);

        // Step 1 belongs to Authority A. User from Authority B cannot approve it.
        $this->expectException(UnauthorizedWorkflowActionException::class);
        $this->approvalService->approveActiveStep($project, $this->authBUser, 'محاولة اعتماد هيئة أخرى');
    }

    public function test_dead_end_route_prevents_draft_closure(): void
    {
        // Deactivate active route for Authority A
        AuthorityApprovalRoute::where('authority_id', $this->authorityA->id)->update(['is_active' => false]);

        $project = Project::create([
            'project_name' => 'مشروع بدون مسار فعال',
            'created_by_user_id' => $this->externalCreator->id,
            'authority_id' => $this->authorityA->id,
            'source_type' => 'external',
            'status' => 'draft',
            'last_saved_step' => 1,
        ]);

        $this->expectException(ConfigurationException::class);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->externalCreator);
    }
}
