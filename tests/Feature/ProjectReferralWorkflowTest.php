<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\ProjectReferral;
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

class ProjectReferralWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected InternalEntity $parentEntity;

    protected InternalEntity $referringEntity;

    protected InternalEntity $consultedEntity;

    protected InternalEntity $unrelatedEntity;

    protected User $referringUser;

    protected User $consultedUser;

    protected User $unrelatedUser;

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
            'referrals.respond',
            'referrals.create',
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
            'name' => 'StandardReferralRole',
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

        $this->referringEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة المحيلة (Referring)',
            'type' => 'assembly',
            'parent_id' => $this->parentEntity->id,
            'is_active' => true,
        ]);

        $this->consultedEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'الجهة المستشارة (Consulted)',
            'type' => 'assembly',
            'parent_id' => $this->parentEntity->id,
            'is_active' => true,
        ]);

        $this->unrelatedEntity = InternalEntity::withoutGlobalScopes()->create([
            'name' => 'جهة خارجية غير مرتبطة (Unrelated)',
            'type' => 'assembly',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $this->referringUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم الجهة المحيلة',
            'username' => 'ref_user_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'ref_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->referringEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->consultedUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم الجهة المستشارة',
            'username' => 'cons_user_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'cons_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->consultedEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $this->unrelatedUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم جهة غير مرتبطة',
            'username' => 'unrel_user_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'unrel_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->unrelatedEntity->id,
            'role_id' => $this->standardRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
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
    }

    protected function createProjectWithApprovalChain(): Project
    {
        $project = Project::withoutGlobalScopes()->create([
            'project_name' => 'مشروع اختبار الاستشارات المتكامل',
            'form_number' => 'PRJ-REF-'.rand(1000, 9999),
            'status' => ProjectStatus::Draft->value,
            'creator_entity_id' => $this->referringEntity->id,
            'internal_entity_id' => $this->referringEntity->id,
            'created_by_user_id' => $this->referringUser->id,
            'created_by' => $this->referringUser->id,
        ]);

        $this->approvalService->closeDraftAndGenerateApprovalChain($project, $this->referringUser);

        return $project->fresh(['projectApprovals']);
    }

    // =========================================================================
    // 1. RECORD CONSULTATION & ISOLATION
    // =========================================================================

    public function test_record_consultation_creates_referral_with_attachments_and_activity_log(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $initialActiveStepId = $project->getActiveApprovalStep()?->id;
        $initialProjectStatus = $project->status;
        $initialApprovalsCount = $project->projectApprovals()->count();

        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة فنية متخصصة بشأن التكاليف',
            ['referrals/test_attachment_1.pdf', 'referrals/test_attachment_2.pdf']
        );

        // 1. Check referral model attributes
        $this->assertInstanceOf(ProjectReferral::class, $referral);
        $this->assertEquals($project->id, $referral->project_id);
        $this->assertEquals($this->referringEntity->id, $referral->referring_entity_id);
        $this->assertEquals($this->consultedEntity->id, $referral->referred_entity_id);
        $this->assertEquals($this->referringUser->id, $referral->referring_user_id);
        $this->assertEquals('pending', $referral->status);
        $this->assertCount(2, $referral->getAllReferralAttachments());

        // 2. Check strict workflow isolation
        $freshProject = $project->fresh(['projectApprovals']);
        $this->assertEquals($initialProjectStatus, $freshProject->status, 'Project status must remain unchanged during referral');
        $this->assertEquals($initialActiveStepId, $freshProject->getActiveApprovalStep()?->id, 'Active approval step must remain unchanged during referral');
        $this->assertEquals($initialApprovalsCount, $freshProject->projectApprovals()->count(), 'Approval chain count must not change during referral');

        // 3. Check activity log
        $this->assertDatabaseHas('project_activity_history', [
            'project_id' => $project->id,
            'user_id' => $this->referringUser->id,
            'action_type' => 'referral',
        ]);
    }

    // =========================================================================
    // 2. AUTHORIZATION (POLICIES) FOR VIEW & RESPOND
    // =========================================================================

    public function test_authorized_users_can_view_referral_in_approval_center(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'طلب استشارة لمعاينة الموقع'
        );

        // 1. Referring user can view
        $response = $this->actingAs($this->referringUser)->get(route('approvals.referral.show', $referral));
        $response->assertStatus(200);
        $response->assertSee('طلب استشارة لمعاينة الموقع');
        $response->assertSee($this->referringEntity->name);
        $response->assertSee($this->consultedEntity->name);

        // 2. Consulted entity user can view
        $response = $this->actingAs($this->consultedUser)->get(route('approvals.referral.show', $referral));
        $response->assertStatus(200);
        $response->assertSee('طلب استشارة لمعاينة الموقع');
        $response->assertSee('الرد على طلب الاستشارة'); // Form is visible to consulted user

        // 3. Admin can view
        $response = $this->actingAs($this->adminUser)->get(route('approvals.referral.show', $referral));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_view_referral(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة سرية'
        );

        $response = $this->actingAs($this->unrelatedUser)->get(route('approvals.referral.show', $referral));
        $response->assertStatus(403);
    }

    public function test_consulted_entity_user_can_respond_to_referral_via_controller(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'طلب الإفادة الفنية'
        );

        $file = UploadedFile::fake()->create('technical_response.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->consultedUser)->post(route('approvals.referral.respond', $referral), [
            'response_text' => 'تمت دراسة الطلب والموافقة الفنية من قبلنا مع التوصية بالتنفيذ المباشر.',
            'status' => 'responded',
            'attachments' => [$file],
        ]);

        $response->assertRedirect(route('consultations.show', $referral));
        $this->assertTrue(
            $response->getSession()->has('success') ||
            str_contains(serialize($response->getSession()->all()), 'تم الرد على الاستشارة بنجاح')
        );

        $freshReferral = $referral->fresh();
        $this->assertEquals('responded', $freshReferral->status);
        $this->assertEquals('تمت دراسة الطلب والموافقة الفنية من قبلنا مع التوصية بالتنفيذ المباشر.', $freshReferral->response_text);
        $this->assertEquals($this->consultedUser->id, $freshReferral->responding_user_id);
        $this->assertNotNull($freshReferral->responded_at);
        $this->assertCount(1, $freshReferral->getAllResponseAttachments());

        // Check Activity history
        $this->assertDatabaseHas('project_activity_history', [
            'project_id' => $project->id,
            'user_id' => $this->consultedUser->id,
            'action_type' => 'referral_response',
        ]);

        // Strict isolation check after response
        $freshProject = $project->fresh(['projectApprovals']);
        $this->assertEquals(ProjectStatus::PendingApproval->value, $freshProject->status);
        $this->assertTrue($freshProject->getActiveApprovalStep()?->is_active ?? false);
    }

    public function test_unauthorized_user_cannot_respond_to_referral(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة سرية'
        );

        // Referring user cannot respond to their own consultation
        $response = $this->actingAs($this->referringUser)->post(route('approvals.referral.respond', $referral), [
            'response_text' => 'رد غير مسموح به',
            'status' => 'responded',
        ]);
        $response->assertStatus(403);

        // Unrelated user cannot respond
        $response = $this->actingAs($this->unrelatedUser)->post(route('approvals.referral.respond', $referral), [
            'response_text' => 'رد من جهة خارجية',
            'status' => 'responded',
        ]);
        $response->assertStatus(403);
    }

    // =========================================================================
    // 3. MULTI-ATTACHMENTS AND HELPERS
    // =========================================================================

    public function test_multi_attachments_stored_and_retrieved_correctly(): void
    {
        $project = $this->createProjectWithApprovalChain();

        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'مراجعة المرفقات المتعددة',
            ['uploads/doc1.pdf', 'uploads/doc2.png']
        );

        $this->assertEquals(['uploads/doc1.pdf', 'uploads/doc2.png'], $referral->getAllReferralAttachments());

        $this->approvalService->respondToConsultation(
            $referral,
            'تم إرفاق الملفات المطلوبة للرد',
            $this->consultedUser,
            'responded',
            ['uploads/response1.pdf', 'uploads/response2.xlsx']
        );

        $freshReferral = $referral->fresh();
        $this->assertEquals(['uploads/response1.pdf', 'uploads/response2.xlsx'], $freshReferral->getAllResponseAttachments());
    }

    // =========================================================================
    // 4. APPROVAL CENTER TABS & DYNAMIC COUNTS FOR REFERRALS
    // =========================================================================
    // 4. CONSULTATIONS CENTER TABS & DYNAMIC COUNTS FOR REFERRALS
    // =========================================================================

    public function test_consultations_center_tabs_display_records_and_dynamic_counts(): void
    {
        $project = $this->createProjectWithApprovalChain();

        // 1. Create a pending referral from referringEntity to consultedEntity
        $referral1 = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة قيد الانتظار واردة للجهة المستشارة'
        );

        // Check for Consulted User on consultations.index (Incoming tab count should be 1, Sent count 0)
        $response = $this->actingAs($this->consultedUser)->get(route('consultations.index', ['tab' => 'incoming']));
        $response->assertStatus(200);
        $response->assertSee('استشارة قيد الانتظار واردة للجهة المستشارة');
        $response->assertSee('الاستشارات الواردة');
        $response->assertSee('الاستشارات الصادرة');
        $response->assertSee('بانتظار الرد');
        $response->assertViewHas('incomingCount', 1);
        $response->assertViewHas('sentCount', 0);
        $response->assertViewHas('pendingCount', 1);

        // Check for Referring User on consultations.index (Sent tab count should be 1, Incoming count 0)
        $response = $this->actingAs($this->referringUser)->get(route('consultations.index', ['tab' => 'sent']));
        $response->assertStatus(200);
        $response->assertSee('استشارة قيد الانتظار واردة للجهة المستشارة');
        $response->assertViewHas('incomingCount', 0);
        $response->assertViewHas('sentCount', 1);

        // Check legacy route redirect: /approvals?tab=referrals_incoming redirects to /consultations?tab=referrals_incoming
        $legacyApprovalResponse = $this->actingAs($this->consultedUser)->get(route('approvals.index', ['tab' => 'referrals_incoming']));
        $legacyApprovalResponse->assertRedirect(route('consultations.index', ['tab' => 'referrals_incoming']));

        // Check legacy project-referrals index redirect: /project-referrals redirects to /consultations
        $legacyReferralsIndexResponse = $this->actingAs($this->consultedUser)->get(route('project-referrals.index'));
        $legacyReferralsIndexResponse->assertRedirect(route('consultations.index'));

        // 2. Respond to the referral
        $this->approvalService->respondToConsultation(
            $referral1,
            'تم الرد رسمياً',
            $this->consultedUser,
            'responded'
        );

        // Check Completed Referrals Tab for both users
        $response = $this->actingAs($this->consultedUser)->get(route('consultations.index', ['tab' => 'completed']));
        $response->assertStatus(200);
        $response->assertSee('استشارة قيد الانتظار واردة للجهة المستشارة');
        $response->assertViewHas('respondedCount', 1);
        $response->assertViewHas('pendingCount', 0);

        $response = $this->actingAs($this->referringUser)->get(route('consultations.index', ['tab' => 'completed']));
        $response->assertStatus(200);
        $response->assertSee('استشارة قيد الانتظار واردة للجهة المستشارة');
        $response->assertViewHas('respondedCount', 1);
    }

    // =========================================================================
    // 5. CLOSING CONSULTATION
    // =========================================================================

    public function test_referring_user_can_close_consultation(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة سيتم إغلاقها'
        );

        $this->approvalService->closeConsultation($referral, $this->referringUser, 'تم الاستغناء عن الاستشارة');

        $freshReferral = $referral->fresh();
        $this->assertEquals('closed', $freshReferral->status);

        $this->assertDatabaseHas('project_activity_history', [
            'project_id' => $project->id,
            'user_id' => $this->referringUser->id,
            'action_type' => 'referral_closed',
        ]);
    }

    public function test_strict_isolation_guarantees_for_create_respond_and_close(): void
    {
        $project = $this->createProjectWithApprovalChain();

        $snapshotProjectState = function () use ($project) {
            $fresh = $project->fresh(['projectApprovals']);

            return [
                'status' => $fresh->status,
                'current_stage' => $fresh->current_stage,
                'current_stage_order' => $fresh->current_stage_order,
                'current_approval_stage_id' => $fresh->current_approval_stage_id,
                'active_approval_id' => $fresh->getActiveApprovalStep()?->id,
                'approval_count' => $fresh->projectApprovals()->count(),
                'approval_step_orders' => $fresh->projectApprovals()->pluck('step_order', 'id')->toArray(),
            ];
        };

        $baseline = $snapshotProjectState();

        // 1. CREATE REFERRAL
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة عزل كامل للمشروع'
        );
        $afterCreate = $snapshotProjectState();
        $this->assertEquals($baseline, $afterCreate, 'Isolation failed during create referral');

        // 2. RESPOND TO REFERRAL
        $this->approvalService->respondToConsultation(
            $referral,
            'تم الرد الفني مع الاحتفاظ بالعزل التام',
            $this->consultedUser,
            'responded'
        );
        $afterRespond = $snapshotProjectState();
        $this->assertEquals($baseline, $afterRespond, 'Isolation failed during respond to referral');

        // 3. CLOSE REFERRAL
        $this->approvalService->closeConsultation(
            $referral,
            $this->referringUser,
            'إغلاق الاستشارة مع التحقق من بقاء مسار الاعتماد ثابتاً'
        );
        $afterClose = $snapshotProjectState();
        $this->assertEquals($baseline, $afterClose, 'Isolation failed during close referral');
    }

    // =========================================================================
    // 6. SEPARATION & INDEPENDENCE ASSERTIONS (REQUIREMENT 9)
    // =========================================================================

    public function test_approvals_index_does_not_display_referral_records(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $uniqueReferralText = 'استشارة_حصرية_غير_موجودة_في_الموافقات_'.uniqid();
        $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            $uniqueReferralText
        );

        $response = $this->actingAs($this->referringUser)->get(route('approvals.index'));
        $response->assertStatus(200);
        $response->assertDontSee($uniqueReferralText);

        $records = $response->viewData('approvalRecords') ?? $response->viewData('projects');
        $this->assertNotEmpty($records);
        foreach ($records as $record) {
            $this->assertInstanceOf(ProjectApproval::class, $record);
            $this->assertNotInstanceOf(ProjectReferral::class, $record);
        }
    }

    public function test_consultations_index_does_not_display_approval_records(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'استشارة لاختبار العزل الكامل للقوائم'
        );

        $response = $this->actingAs($this->consultedUser)->get(route('consultations.index'));
        $response->assertStatus(200);

        $referrals = $response->viewData('referrals');
        $this->assertNotEmpty($referrals);
        foreach ($referrals as $item) {
            $this->assertInstanceOf(ProjectReferral::class, $item);
            $this->assertNotInstanceOf(ProjectApproval::class, $item);
        }

        // Consultations page displays all 5 required statistics
        $response->assertSee('الاستشارات الواردة');
        $response->assertSee('الاستشارات الصادرة');
        $response->assertSee('بانتظار الرد');
        $response->assertSee('تم الرد');
        $response->assertSee('المغلقة');
    }

    public function test_approval_cards_display_project_approval_records(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $response = $this->actingAs($this->referringUser)->get(route('approvals.index'));
        $response->assertStatus(200);

        // Asserts presence of ProjectApproval presentation attributes
        $response->assertSee('الخطوة 1');
        $response->assertSee('عرض المشروع');
        $records = $response->viewData('approvalRecords') ?? $response->viewData('projects');
        $firstRecord = $records->first();
        $this->assertInstanceOf(ProjectApproval::class, $firstRecord);
        $this->assertEquals($project->id, $firstRecord->project_id);
    }

    public function test_consultation_cards_display_project_referral_records(): void
    {
        $project = $this->createProjectWithApprovalChain();
        $referral = $this->approvalService->recordConsultation(
            $project,
            $this->referringUser,
            $this->consultedEntity->id,
            'نص استشارة مستقل تماماً'
        );

        $response = $this->actingAs($this->consultedUser)->get(route('consultations.index'));
        $response->assertStatus(200);

        // Asserts presence of ProjectReferral presentation attributes
        $response->assertSee($project->project_name);
        $response->assertSee($this->referringEntity->name);
        $response->assertSee($this->consultedEntity->name);
        $response->assertSee('عرض الاستشارة');
        $response->assertSee('مدة الانتظار');
        $response->assertSee('نص الاستشارة:');

        $referrals = $response->viewData('referrals');
        $firstItem = $referrals->first();
        $this->assertInstanceOf(ProjectReferral::class, $firstItem);
        $this->assertEquals($referral->id, $firstItem->id);
    }

    public function test_independent_authorization_between_approvals_and_consultations(): void
    {
        // User with referrals.view but NO approvals.view
        $referralOnlyRole = Role::create([
            'name' => 'ReferralOnlyRole_'.uniqid(),
            'is_active' => true,
            'full_access' => false,
        ]);
        $referralPerm = Permission::where('slug', 'referrals.view')->first();
        RolePermission::create([
            'role_id' => $referralOnlyRole->id,
            'permission_id' => $referralPerm->id,
        ]);
        User::incrementRolePermissionsVersion($referralOnlyRole->id);

        $referralOnlyUser = User::withoutGlobalScopes()->create([
            'name' => 'مستخدم استشارات فقط',
            'username' => 'ref_only_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'ref_only_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $this->consultedEntity->id,
            'role_id' => $referralOnlyRole->id,
            'signature' => 'signatures/test_signature.png',
            'signature_path' => 'signatures/test_signature.png',
        ]);

        // User can access consultations.index
        $response = $this->actingAs($referralOnlyUser)->get(route('consultations.index'));
        $response->assertStatus(200);

        // User CANNOT access approvals.index (403 Forbidden)
        $approvalResponse = $this->actingAs($referralOnlyUser)->get(route('approvals.index'));
        $approvalResponse->assertStatus(403);
    }
}
