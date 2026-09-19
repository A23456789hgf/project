<?php

namespace Tests\Feature;

use App\Enums\EntityResponsibilityType;
use App\Enums\ProjectStatus;
use App\Models\Authority;
use App\Models\AuthorityApprovalRoute;
use App\Models\AuthorityApprovalStage;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\User;
use App\Services\ApprovalChainBuilder;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalApprovalPathTest extends TestCase
{
    use RefreshDatabase;

    protected $ministry;

    protected $authorityA;

    protected $authorityB;

    protected $userMinistryTech;

    protected $userMinistryFin;

    protected $userMinistryApp;

    protected $userAuthATech;

    protected $userAuthAFin;

    protected $userAuthAApp;

    protected $userAuthBApp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ministry = InternalEntity::forceCreate(['name' => 'وزارة الزراعة', 'entity_type' => 'Company', 'is_active' => true, 'is_ministry_root' => true]);

        $this->authorityA = Authority::forceCreate(['agency_name' => 'Agency A', 'is_active' => true]);
        $this->authorityB = Authority::forceCreate(['agency_name' => 'Agency B', 'is_active' => true]);

        $this->userMinistryTech = User::forceCreate(['user_id' => 'umt', 'phone' => '111', 'username' => 'min_tech', 'name' => 'Min Tech', 'email' => 'min_tech@example.com', 'password' => 'password', 'entity_id' => $this->ministry->id, 'organization_type' => 'internal', 'responsibility' => 'TECHNICAL', 'status' => 'Active']);
        $this->userMinistryFin = User::forceCreate(['user_id' => 'umf', 'phone' => '112', 'username' => 'min_fin', 'name' => 'Min Fin', 'email' => 'min_fin@example.com', 'password' => 'password', 'entity_id' => $this->ministry->id, 'organization_type' => 'internal', 'responsibility' => 'FINANCIAL', 'status' => 'Active']);
        $this->userMinistryApp = User::forceCreate(['user_id' => 'uma', 'phone' => '113', 'username' => 'min_app', 'name' => 'Min App', 'email' => 'min_app@example.com', 'password' => 'password', 'entity_id' => $this->ministry->id, 'organization_type' => 'internal', 'responsibility' => 'ENTITY_APPROVER', 'status' => 'Active']);

        $this->userAuthATech = User::forceCreate(['user_id' => 'uat', 'phone' => '115', 'username' => 'autha_tech', 'name' => 'AuthA Tech', 'email' => 'autha_tech@example.com', 'password' => 'password', 'authority_id' => $this->authorityA->id, 'organization_type' => 'external', 'responsibility' => 'TECHNICAL', 'status' => 'Active']);
        $this->userAuthAFin = User::forceCreate(['user_id' => 'uaf', 'phone' => '116', 'username' => 'autha_fin', 'name' => 'AuthA Fin', 'email' => 'autha_fin@example.com', 'password' => 'password', 'authority_id' => $this->authorityA->id, 'organization_type' => 'external', 'responsibility' => 'FINANCIAL', 'status' => 'Active']);
        $this->userAuthAApp = User::forceCreate(['user_id' => 'uaa', 'phone' => '117', 'username' => 'autha_app', 'name' => 'AuthA App', 'email' => 'autha_app@example.com', 'password' => 'password', 'authority_id' => $this->authorityA->id, 'organization_type' => 'external', 'responsibility' => 'ENTITY_APPROVER', 'status' => 'Active']);

        $this->userAuthBApp = User::forceCreate(['user_id' => 'uba', 'phone' => '118', 'username' => 'authb_app', 'name' => 'AuthB App', 'email' => 'authb_app@example.com', 'password' => 'password', 'authority_id' => $this->authorityB->id, 'organization_type' => 'external', 'responsibility' => 'ENTITY_APPROVER', 'status' => 'Active']);

        // Setup Ministry Stages
        EntityApprovalStage::forceCreate(['entity_id' => $this->ministry->id, 'stage' => EntityResponsibilityType::TechnicalReview->value, 'stage_order' => 1, 'is_active' => true, 'responsible_user_id' => $this->userMinistryTech->id]);
        EntityApprovalStage::forceCreate(['entity_id' => $this->ministry->id, 'stage' => EntityResponsibilityType::FinancialReview->value, 'stage_order' => 2, 'is_active' => true, 'responsible_user_id' => $this->userMinistryFin->id]);
        EntityApprovalStage::forceCreate(['entity_id' => $this->ministry->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'is_active' => true, 'responsible_user_id' => $this->userMinistryApp->id]);

        // Setup Authority A Stages
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityA->id, 'stage' => EntityResponsibilityType::TechnicalReview->value, 'stage_order' => 1, 'is_active' => true, 'responsible_user_id' => $this->userAuthATech->id]);
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityA->id, 'stage' => EntityResponsibilityType::FinancialReview->value, 'stage_order' => 2, 'is_active' => true, 'responsible_user_id' => $this->userAuthAFin->id]);
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityA->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'is_active' => true, 'responsible_user_id' => $this->userAuthAApp->id]);

        // Setup Authority B Stages (Only Approval)
        AuthorityApprovalStage::forceCreate(['authority_id' => $this->authorityB->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 1, 'is_active' => true, 'responsible_user_id' => $this->userAuthBApp->id]);
    }

    public function test_positive_explicit_path_a_to_b_to_ministry()
    {
        // A -> B -> Ministry
        AuthorityApprovalRoute::create(['authority_id' => $this->authorityA->id, 'destination_type' => 'authority', 'destination_authority_id' => $this->authorityB->id, 'is_active' => true]);
        AuthorityApprovalRoute::create(['authority_id' => $this->authorityB->id, 'destination_type' => 'ministry', 'is_active' => true]);

        $builder = new ApprovalChainBuilder;
        $chain = $builder->buildExternalChain($this->authorityA->id);
        $this->assertCount(7, $chain);
        // A (Tech, Fin, App) -> B (App) -> Ministry (Tech, Fin, App)
        $this->assertEquals($this->authorityA->id, $chain[0]['authority_id']);
        $this->assertEquals('technical_review', $chain[0]['phase']);

        $this->assertEquals($this->authorityA->id, $chain[1]['authority_id']);
        $this->assertEquals('financial_review', $chain[1]['phase']);

        $this->assertEquals($this->authorityA->id, $chain[2]['authority_id']);
        $this->assertEquals('stage_approval', $chain[2]['phase']);

        $this->assertEquals($this->authorityB->id, $chain[3]['authority_id']);
        $this->assertEquals('stage_approval', $chain[3]['phase']);

        $this->assertEquals($this->ministry->id, $chain[4]['entity_id']);
        $this->assertEquals('technical_review', $chain[4]['phase']);

        $this->assertEquals($this->ministry->id, $chain[5]['entity_id']);
        $this->assertEquals('financial_review', $chain[5]['phase']);

        $this->assertEquals($this->ministry->id, $chain[6]['entity_id']);
        $this->assertEquals('stage_approval', $chain[6]['phase']);
    }

    public function test_broken_route_a_to_b_but_b_is_dead_end()
    {
        // A -> B, B -> nowhere
        AuthorityApprovalRoute::create(['authority_id' => $this->authorityA->id, 'destination_type' => 'authority', 'destination_authority_id' => $this->authorityB->id, 'is_active' => true]);

        $builder = new ApprovalChainBuilder;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('مسار اعتماد الجهة الخارجية غير صالح: لا يوجد مسار فعال (Active Route) للجهة الخارجية: Agency B.');
        $builder->buildExternalChain($this->authorityA->id);
    }

    public function test_explicit_direct_path_a_to_ministry()
    {
        // A -> Ministry
        AuthorityApprovalRoute::create(['authority_id' => $this->authorityA->id, 'destination_type' => 'ministry', 'is_active' => true]);

        $builder = new ApprovalChainBuilder;
        $chain = $builder->buildExternalChain($this->authorityA->id);

        $this->assertCount(6, $chain);
        // A (Tech, Fin, App) -> Ministry (Tech, Fin, App)
        $this->assertEquals($this->authorityA->id, $chain[0]['authority_id']);
        $this->assertEquals('technical_review', $chain[0]['phase']);

        $this->assertEquals($this->authorityA->id, $chain[2]['authority_id']);
        $this->assertEquals('stage_approval', $chain[2]['phase']);

        $this->assertEquals($this->ministry->id, $chain[3]['entity_id']);
        $this->assertEquals('technical_review', $chain[3]['phase']);

        $this->assertEquals($this->ministry->id, $chain[5]['entity_id']);
        $this->assertEquals('stage_approval', $chain[5]['phase']);
    }

    public function test_execution_guard_explicit()
    {
        AuthorityApprovalRoute::create(['authority_id' => $this->authorityA->id, 'destination_type' => 'ministry', 'is_active' => true]);

        $project = Project::factory()->create([
            'authority_id' => $this->authorityA->id,
            'source_type' => 'external',
            'status' => 'draft',
            'created_by_user_id' => $this->userAuthATech->id,
        ]);

        $approvalService = app(ApprovalService::class);
        $approvalService->closeDraftAndGenerateApprovalChain($project, $this->userAuthATech);

        // External A Tech
        $approvalService->approveActiveStep($project, $this->userAuthATech, 'OK');
        $this->assertNotEquals(ProjectStatus::InExecution->value, $project->fresh()->status);

        // External A Fin
        $approvalService->approveActiveStep($project, $this->userAuthAFin, 'OK');
        $this->assertNotEquals(ProjectStatus::InExecution->value, $project->fresh()->status);

        // External A App
        $approvalService->approveActiveStep($project, $this->userAuthAApp, 'OK');
        $this->assertNotEquals(ProjectStatus::InExecution->value, $project->fresh()->status);

        // Ministry Tech
        $approvalService->approveActiveStep($project, $this->userMinistryTech, 'OK');
        $this->assertNotEquals(ProjectStatus::InExecution->value, $project->fresh()->status);

        // Ministry Fin
        $approvalService->approveActiveStep($project, $this->userMinistryFin, 'OK');
        $this->assertNotEquals(ProjectStatus::InExecution->value, $project->fresh()->status);

        // Ministry App (Final)
        $res = $approvalService->approveActiveStep($project, $this->userMinistryApp, 'OK');
        $this->assertTrue($res['is_final']);
        $this->assertEquals(ProjectStatus::InExecution->value, $project->fresh()->status);
    }
}
