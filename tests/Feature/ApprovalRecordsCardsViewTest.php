<?php

namespace Tests\Feature;

use App\Enums\EntityResponsibilityType;
use App\Enums\ProjectStatus;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\Project;
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

class ApprovalRecordsCardsViewTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected InternalEntity $parentEntity;

    protected InternalEntity $childEntity;

    protected InternalEntity $otherEntity;

    protected User $childCreatorUser;

    protected User $childReviewerUser;

    protected User $parentUser;

    protected User $otherUser;

    protected User $adminUser;

    protected Role $standardRole;

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

        $this->standardRole = Role::create([
            'name' => 'CardViewStandardRole',
            'is_active' => true,
            'full_access' => false,
        ]);

        $permissions = Permission::all();
        foreach ($permissions as $perm) {
            RolePermission::firstOrCreate([
                'role_id' => $this->standardRole->id,
                'permission_id' => $perm->id,
            ]);
        }
        User::incrementRolePermissionsVersion($this->standardRole->id);

        $this->parentEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الاتحاد العام (Parent)',
            'type' => 'union',
            'is_active' => true,
        ]);

        $this->childEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجمعية المحلية (Child)',
            'type' => 'assembly',
            'parent_id' => $this->parentEntity->id,
            'is_active' => true,
        ]);

        $this->otherEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'جهة أخرى منفصلة (Other)',
            'type' => 'assembly',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $this->childCreatorUser = User::withoutGlobalScopes()->create([
            'name' => 'منشئ المشروع',
            'username' => 'creator_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'creator_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->childEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->childReviewerUser = User::withoutGlobalScopes()->create([
            'name' => 'مراجع الجمعية',
            'username' => 'reviewer_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'reviewer_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->childEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->parentUser = User::withoutGlobalScopes()->create([
            'name' => 'مراجع الاتحاد',
            'username' => 'parent_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'parent_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->parentEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->otherUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم جهة خارجية',
            'username' => 'other_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'other_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->otherEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $adminRole = Role::create([
            'name' => 'AdminRole',
            'is_active' => true,
            'full_access' => true,
        ]);

        $this->adminUser = User::withoutGlobalScopes()->create([
            'name' => 'المدير العام للنظام',
            'username' => 'admin_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
            'user_type' => 'admin',
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
                'responsible_user_id' => $this->childReviewerUser->id,
            ]);

            EntityApprovalStage::create([
                'entity_id' => $this->parentEntity->id,
                'stage' => $type->value,
                'stage_order' => $type->stageOrder(),
                'responsible_user_id' => $this->parentUser->id,
            ]);
        }
    }

    protected function createProjectWithChain(): Project
    {
        $project = Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع بطاقات سجلات الاعتماد الميداني',
            'form_number' => 'PRJ-CARD-'.rand(1000, 9999),
            'status' => ProjectStatus::Draft->value,
            'creator_entity_id' => $this->childEntity->id,
            'internal_entity_id' => $this->childEntity->id,
            'created_by_user_id' => $this->childCreatorUser->id,
            'created_by' => $this->childCreatorUser->id,
        ]);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->childCreatorUser);

        return $project->fresh(['projectApprovals']);
    }

    // =========================================================================
    // 1. APPROVAL RECORDS & CARDS VIEW RENDERING
    // =========================================================================

    public function test_approval_records_rendered_as_table_with_required_fields(): void
    {
        $project = $this->createProjectWithChain();
        $activeStep = $project->getActiveApprovalStep();

        $response = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action']));
        $response->assertStatus(200);

        // 1. Project name & code
        $response->assertSee($project->project_name);
        $response->assertSee($project->form_number);

        // 2. Entity name
        $response->assertSee($this->childEntity->name);

        // 3. Approval phase label
        $response->assertSee($activeStep->getPhaseLabel());

        $response->assertSee('<table', false);
        $response->assertSee('رقم / المشروع');
        $response->assertSee('الجهة المخصصة');
        $response->assertDontSee('approval-record-card');

        $response->assertSee(route('approvals.show', $project->id));
        $response->assertSee('التفاصيل');
    }

    // =========================================================================
    // 2. ACTIVE APPROVAL CARD INDICATORS
    // =========================================================================

    public function test_only_active_approval_record_is_rendered(): void
    {
        $project = $this->createProjectWithChain();
        $activeStep = $project->getActiveApprovalStep();

        $response = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action']));
        $response->assertStatus(200);

        $records = $response->viewData('approvalRecords');
        $this->assertCount(1, $records);
        $this->assertTrue($records->contains('id', $activeStep->id));
    }

    // =========================================================================
    // 3. DIFFERENT APPROVAL CARD STATUSES
    // =========================================================================

    public function test_completed_record_is_replaced_by_the_new_active_step(): void
    {
        $project = $this->createProjectWithChain();
        $completedStep = $project->getActiveApprovalStep();

        $this->approvalService->approveActiveStep($project, $this->childReviewerUser, 'تم الاعتماد');
        $completedResponse = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'completed']));
        $completedResponse->assertStatus(200);
        $records = $completedResponse->viewData('approvalRecords');
        $this->assertFalse($records->contains('id', $completedStep->id));
        $this->assertTrue($records->every(fn ($record) => $record->is_active && $record->status === 'pending'));
    }

    // =========================================================================
    // 4. AUTHORIZATION: ACTION BUTTON VISIBILITY
    // =========================================================================

    public function test_action_button_visible_only_for_authorized_user(): void
    {
        $project = $this->createProjectWithChain();

        // 1. Authorized user (child reviewer whose entity matches Step 1)
        $authorizedResponse = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action']));
        $authorizedResponse->assertStatus(200);
        $authorizedResponse->assertSee(route('approvals.show', $project));

        // 2. Unauthorized user (parent user when step is with child entity)
        $unauthorizedResponse = $this->actingAs($this->parentUser)->get(route('approvals.index', ['tab' => 'waiting_others']));
        $unauthorizedResponse->assertStatus(200);
        $unauthorizedResponse->assertDontSee($project->project_name);
    }

    // =========================================================================
    // 5. PAGINATION WORKS
    // =========================================================================

    public function test_pagination_works_on_approval_records(): void
    {
        // Generate 15 projects with approval chains (paginate is 12)
        for ($i = 1; $i <= 14; $i++) {
            $p = Project::withoutGlobalScopes()->create([
                'project_name' => "مشروع ترقيم الصفحات {$i}",
                'form_number' => "PRJ-PAG-{$i}",
                'status' => ProjectStatus::Draft->value,
                'creator_entity_id' => $this->childEntity->id,
                'internal_entity_id' => $this->childEntity->id,
                'created_by_user_id' => $this->childCreatorUser->id,
                'created_by' => $this->childCreatorUser->id,
            ]);
            $this->approvalService->closeDraftAndGenerateApprovalChain($p, $this->childCreatorUser);
        }

        // Page 1
        $responsePage1 = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action', 'page' => 1]));
        $responsePage1->assertStatus(200);

        // Page 2
        $responsePage2 = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action', 'page' => 2]));
        $responsePage2->assertStatus(200);
    }

    // =========================================================================
    // 6. FILTERS WORK ON APPROVAL RECORDS
    // =========================================================================

    public function test_filters_work_on_approval_records(): void
    {
        $project1 = $this->createProjectWithChain();

        $project2 = Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع مميز للبحث والتصفية الفردية',
            'form_number' => 'PRJ-SPECIFIC-99',
            'status' => ProjectStatus::Draft->value,
            'creator_entity_id' => $this->childEntity->id,
            'internal_entity_id' => $this->childEntity->id,
            'created_by_user_id' => $this->childCreatorUser->id,
            'created_by' => $this->childCreatorUser->id,
        ]);
        $this->approvalService->closeDraftAndGenerateApprovalChain($project2, $this->childCreatorUser);

        // Filter by Search
        $searchResponse = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', [
            'tab' => 'my_action',
            'search' => 'PRJ-SPECIFIC-99',
        ]));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('مشروع مميز للبحث والتصفية الفردية');
        $searchResponse->assertDontSee($project1->form_number);

        // Filter by Entity ID
        $entityResponse = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', [
            'tab' => 'my_action',
            'entity_id' => $this->childEntity->id,
        ]));
        $entityResponse->assertStatus(200);
        $entityResponse->assertSee($project1->project_name);

        // Filter by Status = active
        $statusResponse = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', [
            'tab' => 'my_action',
            'status' => 'active',
        ]));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee($project1->project_name);
    }

    // =========================================================================
    // 7. CARD CLICK NAVIGATES TO APPROVALS.SHOW DETAIL
    // =========================================================================

    public function test_card_click_navigates_to_approvals_show(): void
    {
        $project = $this->createProjectWithChain();

        $response = $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action']));
        $response->assertStatus(200);
        $response->assertSee(route('approvals.show', $project->id));

        // Follow navigation link
        $showResponse = $this->actingAs($this->childReviewerUser)->get(route('approvals.show', $project));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($project->project_name);
        $showResponse->assertDontSee('مسار وسلسلة الاعتمادات');
        $showResponse->assertSee('لوحة اتخاذ القرار');
    }

    // =========================================================================
    // 8. WORKFLOW REMAINS UNAFFECTED BY APPROVAL RECORDS PRESENTATION
    // =========================================================================

    public function test_workflow_remains_unaffected_by_approval_records_view(): void
    {
        $project = $this->createProjectWithChain();

        $initialState = [
            'status' => $project->status,
            'current_stage' => $project->current_stage,
            'current_stage_order' => $project->current_stage_order,
            'active_step_id' => $project->getActiveApprovalStep()?->id,
            'approval_count' => $project->projectApprovals()->count(),
            'steps_orders' => $project->projectApprovals()->pluck('step_order', 'id')->toArray(),
        ];

        // Perform multiple view and filter requests
        $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'my_action']));
        $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'waiting_others']));
        $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'completed']));
        $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'rejected']));
        $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['tab' => 'returned']));
        $this->actingAs($this->childReviewerUser)->get(route('approvals.index', ['search' => 'بطاقات']));

        $fresh = $project->fresh(['projectApprovals']);
        $afterState = [
            'status' => $fresh->status,
            'current_stage' => $fresh->current_stage,
            'current_stage_order' => $fresh->current_stage_order,
            'active_step_id' => $fresh->getActiveApprovalStep()?->id,
            'approval_count' => $fresh->projectApprovals()->count(),
            'steps_orders' => $fresh->projectApprovals()->pluck('step_order', 'id')->toArray(),
        ];

        $this->assertEquals($initialState, $afterState, 'Workflow state must remain 100% untouched by approval records view');
    }
}
