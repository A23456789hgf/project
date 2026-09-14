<?php

namespace Tests\Feature;

use App\Models\ExecutiveActionCost;
use App\Models\ExecutiveActivity;
use App\Models\ExecutiveActivityAction;
use App\Models\FinancialItem;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\PreliminaryActivity;
use App\Models\PreliminaryCost;
use App\Models\PreliminaryProcedure;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\ProjectCost;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\PermissionResolver;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnicalAndFinancialReviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected InternalEntity $entity;

    protected User $creatorUser;

    protected User $technicalUser;

    protected User $financialUser;

    protected User $unauthorizedUser;

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
            'projects.view',
            'projects.view-details',
            'reviews.technical',
            'reviews.financial',
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

        $this->entity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة التجريبية للمراجعات',
            'type' => 'assembly',
            'is_active' => true,
        ]);

        // Roles
        $techRole = Role::create([
            'name' => 'TechnicalReviewerRole',
            'is_active' => true,
            'full_access' => false,
        ]);
        $finRole = Role::create([
            'name' => 'FinancialReviewerRole',
            'is_active' => true,
            'full_access' => false,
        ]);
        $noPermRole = Role::create([
            'name' => 'NoPermRole',
            'is_active' => true,
            'full_access' => false,
        ]);

        $grantPerms = function (Role $role, array $slugs) {
            foreach ($slugs as $slug) {
                $p = Permission::where('slug', $slug)->first();
                if ($p) {
                    RolePermission::firstOrCreate([
                        'role_id' => $role->id,
                        'permission_id' => $p->id,
                    ]);
                }
            }
            User::incrementRolePermissionsVersion($role->id);
        };

        $grantPerms($techRole, ['main_modules.projects', 'approvals.view', 'projects.view', 'projects.view-details', 'reviews.technical']);
        $grantPerms($finRole, ['main_modules.projects', 'approvals.view', 'projects.view', 'projects.view-details', 'reviews.financial']);
        $grantPerms($noPermRole, ['main_modules.projects', 'projects.view']);

        $this->creatorUser = User::withoutGlobalScopes()->create([
            'name' => 'منشئ المشروع',
            'username' => 'creator_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'creator_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->entity->id,
            'role_id' => $techRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->technicalUser = User::withoutGlobalScopes()->create([
            'name' => 'المراجع الفني',
            'username' => 'tech_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'tech_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->entity->id,
            'role_id' => $techRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->financialUser = User::withoutGlobalScopes()->create([
            'name' => 'المراجع المالي',
            'username' => 'fin_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'fin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->entity->id,
            'role_id' => $finRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->unauthorizedUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم غير مصرح',
            'username' => 'unauth_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'unauth_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->entity->id,
            'role_id' => $noPermRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);
    }

    protected function createCompleteProject(): array
    {
        $project = Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع اختبار المراجعة الفنية والمالية',
            'form_number' => 'PRJ-TF-'.rand(1000, 9999),
            'status' => 'financial_technical_review',
            'creator_entity_id' => $this->entity->id,
            'internal_entity_id' => $this->entity->id,
            'created_by_user_id' => $this->creatorUser->id,
            'created_by' => $this->creatorUser->id,
            'total_cost' => 1500,
        ]);

        $projectCost = ProjectCost::create([
            'project_id' => $project->id,
            'total_cost' => 1500,
            'year_type' => 'gregorian',
            'approval_date_hijri' => '1447-01-01',
            'approval_year_gregorian' => 2026,
        ]);

        $preliminaryActivity = PreliminaryActivity::create([
            'project_id' => $project->id,
            'name' => 'النشاط التمهيدي الأصلي',
        ]);

        $preliminaryProcedure = PreliminaryProcedure::create([
            'project_id' => $project->id,
            'activity_id' => $preliminaryActivity->id,
            'procedure_name' => 'الإجراء التمهيدي الأصلي',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
            'duration_days' => 9,
        ]);

        $financialItem = FinancialItem::create([
            'name' => 'بند مالي تجريبي',
            'code' => 'FIN-TEST-01',
            'is_active' => true,
            'status' => 'active',
        ]);

        $preliminaryCost = PreliminaryCost::create([
            'project_id' => $project->id,
            'activity_id' => $preliminaryActivity->id,
            'procedure_id' => $preliminaryProcedure->id,
            'financial_item_id' => $financialItem->id,
            'quantity' => 2,
            'amount' => 250,
            'total' => 500,
        ]);

        $executiveActivity = ExecutiveActivity::create([
            'project_id' => $project->id,
            'name' => 'النشاط التنفيذي الأصلي',
        ]);

        $executiveAction = ExecutiveActivityAction::create([
            'executive_activity_id' => $executiveActivity->id,
            'action' => 'الإجراء التنفيذي الأصلي',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-30',
        ]);

        $executiveCost = ExecutiveActionCost::create([
            'project_id' => $project->id,
            'executive_activity_id' => $executiveActivity->id,
            'executive_activity_action_id' => $executiveAction->id,
            'financial_item_id' => $financialItem->id,
            'quantity' => 1,
            'amount' => 1000,
            'total' => 1000,
        ]);

        $approval = ProjectApproval::create([
            'project_id' => $project->id,
            'entity_id' => $this->entity->id,
            'step_order' => 1,
            'status' => 'financial_technical_review',
            'is_active' => true,
            'is_completed' => false,
            'phase' => 'technical_review',
        ]);

        return compact(
            'project',
            'projectCost',
            'preliminaryActivity',
            'preliminaryProcedure',
            'preliminaryCost',
            'executiveActivity',
            'executiveAction',
            'executiveCost',
            'approval'
        );
    }

    public function test_technical_reviewer_can_update_activities_procedures_and_attachments_without_affecting_costs(): void
    {
        extract($this->createCompleteProject());

        $file = UploadedFile::fake()->create('tech_plan.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->technicalUser)->post(route('projects.review.technical.submit', $project), [
            'status' => 'approved',
            'review_notes' => 'الملاحظات الفنية للمشروع معتمدة بالكامل',
            'attachment' => $file,
            'preliminary_activities' => [
                $preliminaryActivity->id => [
                    'name' => 'النشاط التمهيدي المحدّث من المراجع الفني',
                ],
            ],
            'preliminary_procedures' => [
                $preliminaryProcedure->id => [
                    'procedure_name' => 'الإجراء التمهيدي المحدّث فنياً',
                    'start_date' => '2026-10-01',
                    'end_date' => '2026-10-15',
                ],
            ],
            'executive_activities' => [
                $executiveActivity->id => [
                    'name' => 'النشاط التنفيذي المحدّث من المراجع الفني',
                ],
            ],
            'executive_actions' => [
                $executiveAction->id => [
                    'action' => 'الإجراء التنفيذي المحدّث فنياً',
                    'start_date' => '2026-11-01',
                    'end_date' => '2026-11-20',
                ],
            ],
            // Attempt to tamper with financial costs during technical review
            'preliminary_costs' => [
                $preliminaryCost->id => [
                    'quantity' => 999,
                    'amount' => 999999,
                ],
            ],
            'executive_costs' => [
                $executiveCost->id => [
                    'amount' => 888888,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        // 1. Assert Technical Data IS Updated
        $this->assertEquals('النشاط التمهيدي المحدّث من المراجع الفني', $preliminaryActivity->fresh()->name);
        $this->assertEquals('الإجراء التمهيدي المحدّث فنياً', $preliminaryProcedure->fresh()->procedure_name);
        $this->assertEquals('2026-10-01', $preliminaryProcedure->fresh()->start_date->format('Y-m-d'));
        $this->assertEquals('النشاط التنفيذي المحدّث من المراجع الفني', $executiveActivity->fresh()->name);
        $this->assertEquals('الإجراء التنفيذي المحدّث فنياً', $executiveAction->fresh()->action);

        // 2. Assert Financial Costs ARE STRICTLY UNCHANGED (Untouched)
        $this->assertEquals(2, $preliminaryCost->fresh()->quantity, 'Preliminary cost quantity must remain untouched during technical review');
        $this->assertEquals(250, (float) $preliminaryCost->fresh()->amount, 'Preliminary cost amount must remain untouched during technical review');
        $this->assertEquals(500, (float) $preliminaryCost->fresh()->total, 'Preliminary cost total must remain untouched during technical review');
        $this->assertEquals(1000, (float) $executiveCost->fresh()->total, 'Executive cost total must remain untouched during technical review');

        // 3. Assert Approval has technical notes and reviewer recorded
        $freshApproval = $approval->fresh();
        $this->assertEquals('approved', $freshApproval->technical_review_status);
        $this->assertEquals($this->technicalUser->id, $freshApproval->technical_reviewer_id);
        $this->assertEquals('الملاحظات الفنية للمشروع معتمدة بالكامل', $freshApproval->technical_notes);
        $this->assertNotNull($freshApproval->technical_attachment);
    }

    public function test_financial_reviewer_can_update_costs_and_attachments_without_affecting_technical_data(): void
    {
        extract($this->createCompleteProject());

        $file = UploadedFile::fake()->create('financial_audit.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->financialUser)->post(route('projects.review.financial.submit', $project), [
            'status' => 'approved',
            'review_notes' => 'المراجعة المالية معتمدة بعد تعديل أسعار البنود',
            'attachment' => $file,
            'preliminary_costs' => [
                $preliminaryCost->id => [
                    'quantity' => 4,
                    'amount' => 300, // new total = 1200
                ],
            ],
            'executive_costs' => [
                $executiveCost->id => [
                    'quantity' => 2,
                    'amount' => 1500, // new total = 3000
                ],
            ],
            // Attempt to tamper with technical activity names during financial review
            'preliminary_activities' => [
                $preliminaryActivity->id => [
                    'name' => 'محاولة اختراق تعديل النشاط من المراجع المالي',
                ],
            ],
            'executive_actions' => [
                $executiveAction->id => [
                    'action' => 'محاولة غير مصرح بها للإجراء',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        // 1. Assert Financial Costs ARE Updated
        $freshPrelimCost = $preliminaryCost->fresh();
        $this->assertEquals(4, $freshPrelimCost->quantity);
        $this->assertEquals(300, (float) $freshPrelimCost->amount);
        $this->assertEquals(1200, (float) $freshPrelimCost->total);

        $freshExecCost = $executiveCost->fresh();
        $this->assertEquals(2, $freshExecCost->quantity);
        $this->assertEquals(1500, (float) $freshExecCost->amount);
        $this->assertEquals(3000, (float) $freshExecCost->total);

        // Assert Project Total Cost is recalculated (1200 + 3000 = 4200)
        $this->assertEquals(4200, (float) $project->fresh()->total_cost);

        // 2. Assert Technical Data IS STRICTLY UNCHANGED (Untouched)
        $this->assertEquals('النشاط التمهيدي الأصلي', $preliminaryActivity->fresh()->name, 'Activity name must remain untouched during financial review');
        $this->assertEquals('الإجراء التنفيذي الأصلي', $executiveAction->fresh()->action, 'Action name must remain untouched during financial review');

        // 3. Assert Approval has financial notes and reviewer recorded
        $freshApproval = $approval->fresh();
        $this->assertEquals('approved', $freshApproval->financial_review_status);
        $this->assertEquals($this->financialUser->id, $freshApproval->financial_reviewer_id);
        $this->assertEquals('المراجعة المالية معتمدة بعد تعديل أسعار البنود', $freshApproval->financial_notes);
        $this->assertNotNull($freshApproval->financial_attachment);
    }

    public function test_authorization_prevents_unauthorized_users_from_reviewing(): void
    {
        extract($this->createCompleteProject());

        // Unauthorized user trying to view technical review
        $response = $this->actingAs($this->unauthorizedUser)->get(route('projects.review.technical', $project));
        $response->assertStatus(403);

        // Unauthorized user trying to view financial review
        $response = $this->actingAs($this->unauthorizedUser)->get(route('projects.review.financial', $project));
        $response->assertStatus(403);

        // Technical user cannot view financial review
        $response = $this->actingAs($this->technicalUser)->get(route('projects.review.financial', $project));
        $response->assertStatus(403);

        // Financial user cannot view technical review
        $response = $this->actingAs($this->financialUser)->get(route('projects.review.technical', $project));
        $response->assertStatus(403);
    }
}
