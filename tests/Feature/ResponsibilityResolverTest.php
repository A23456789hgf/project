<?php

namespace Tests\Feature;

use App\Enums\EntityResponsibilityType;
use App\Enums\UserResponsibilityType;
use App\Exceptions\ConfigurationException;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\User;
use App\Services\ResponsibilityResolution;
use App\Services\ResponsibilityResolver;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResponsibilityResolverTest extends TestCase
{
    use RefreshDatabase;

    private ResponsibilityResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(ResponsibilityResolver::class);
        Cache::flush();
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    private function createEntityWithStage(
        EntityResponsibilityType $stageType,
        ?User $responsibleUser = null
    ): array {
        $entity = InternalEntity::factory()->create();

        $stage = EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => $stageType->value,
            'stage_order' => $stageType->stageOrder(),
            'responsible_user_id' => $responsibleUser?->id,
            'created_by' => null,
        ]);

        return [$entity, $stage];
    }

    // ----------------------------------------------------------------
    // 1. Basic resolution from entity_approval_stages
    // ----------------------------------------------------------------

    public function test_returns_configured_user_from_entity_approval_stages(): void
    {
        $entity = InternalEntity::factory()->create();
        $user = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active', 'responsibility' => UserResponsibilityType::Technical->value]);

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::TechnicalReview->value,
            'stage_order' => 1,
            'responsible_user_id' => $user->id,
        ]);

        $resolution = $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::TechnicalReview);

        $this->assertSame($user->id, $resolution->user?->id);
        $this->assertSame(ResponsibilityResolution::Resolved, $resolution->status);
        $this->assertSame('entity_stage_config', $resolution->source);
    }

    public function test_resolves_each_stage_type_independently(): void
    {
        $entity = InternalEntity::factory()->create();
        $technicalUser = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active', 'responsibility' => UserResponsibilityType::Technical->value]);
        $financialUser = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active', 'responsibility' => UserResponsibilityType::Financial->value]);
        $approverUser = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active', 'responsibility' => UserResponsibilityType::EntityApprover->value]);

        EntityApprovalStage::create(['entity_id' => $entity->id, 'stage' => EntityResponsibilityType::TechnicalReview->value, 'stage_order' => 1, 'responsible_user_id' => $technicalUser->id]);
        EntityApprovalStage::create(['entity_id' => $entity->id, 'stage' => EntityResponsibilityType::FinancialReview->value, 'stage_order' => 2, 'responsible_user_id' => $financialUser->id]);
        EntityApprovalStage::create(['entity_id' => $entity->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'responsible_user_id' => $approverUser->id]);

        $this->assertSame($technicalUser->id, $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::TechnicalReview)->user?->id);
        $this->assertSame($financialUser->id, $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::FinancialReview)->user?->id);
        $this->assertSame($approverUser->id, $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::Approval)->user?->id);
    }

    // ----------------------------------------------------------------
    // 2. No responsible user configured → NoResponsibleUser
    // ----------------------------------------------------------------

    public function test_returns_no_responsible_user_when_stage_not_configured(): void
    {
        $entity = InternalEntity::factory()->create();
        // No EntityApprovalStage rows – stage not configured

        $resolution = $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::Approval);

        $this->assertNull($resolution->user);
        $this->assertSame(ResponsibilityResolution::NoResponsibleUser, $resolution->status);
    }

    public function test_returns_no_responsible_user_when_stage_enabled_but_no_user_assigned(): void
    {
        $entity = InternalEntity::factory()->create();

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => null, // explicitly null
        ]);

        $resolution = $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::Approval);

        $this->assertNull($resolution->user);
        $this->assertSame(ResponsibilityResolution::NoResponsibleUser, $resolution->status);
    }

    // ----------------------------------------------------------------
    // 3. Inactive user → NoResponsibleUser (no silent fallback)
    // ----------------------------------------------------------------

    public function test_inactive_configured_user_returns_no_responsible_user(): void
    {
        $entity = InternalEntity::factory()->create();
        $inactiveUser = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Disabled']);

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => $inactiveUser->id,
        ]);

        $resolution = $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::Approval);

        $this->assertNull($resolution->user);
        $this->assertSame(ResponsibilityResolution::NoResponsibleUser, $resolution->status);
    }

    // ----------------------------------------------------------------
    // 4. Cross-entity user → NoResponsibleUser
    // ----------------------------------------------------------------

    public function test_does_not_resolve_user_from_different_entity(): void
    {
        $entityA = InternalEntity::factory()->create();
        $entityB = InternalEntity::factory()->create();
        $userFromB = User::factory()->create(['entity_id' => $entityB->id, 'status' => 'Active']);

        EntityApprovalStage::create([
            'entity_id' => $entityA->id,
            'stage' => EntityResponsibilityType::TechnicalReview->value,
            'stage_order' => 1,
            'responsible_user_id' => $userFromB->id,
        ]);

        $resolution = $this->resolver->resolveResponsibleUser($entityA, EntityResponsibilityType::TechnicalReview);

        $this->assertNull($resolution->user);
        $this->assertSame(ResponsibilityResolution::NoResponsibleUser, $resolution->status);
    }

    // ----------------------------------------------------------------
    // 5. Same stage – different entities → different users
    // ----------------------------------------------------------------

    public function test_same_stage_resolves_to_different_users_for_different_entities(): void
    {
        $entityA = InternalEntity::factory()->create();
        $entityB = InternalEntity::factory()->create();
        $approverA = User::factory()->create(['entity_id' => $entityA->id, 'status' => 'Active']);
        $approverB = User::factory()->create(['entity_id' => $entityB->id, 'status' => 'Active']);

        EntityApprovalStage::create(['entity_id' => $entityA->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'responsible_user_id' => $approverA->id]);
        EntityApprovalStage::create(['entity_id' => $entityB->id, 'stage' => EntityResponsibilityType::Approval->value, 'stage_order' => 3, 'responsible_user_id' => $approverB->id]);

        $this->assertSame($approverA->id, $this->resolver->resolveResponsibleUser($entityA, EntityResponsibilityType::Approval)->user?->id);
        $this->assertSame($approverB->id, $this->resolver->resolveResponsibleUser($entityB, EntityResponsibilityType::Approval)->user?->id);
    }

    // ----------------------------------------------------------------
    // 6. Snapshot in ProjectApproval takes priority over stage config
    // ----------------------------------------------------------------

    public function test_snapshot_in_project_approval_overrides_stage_config(): void
    {
        $entity = InternalEntity::factory()->create();
        $configuredUser = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active']);
        $snapshotUser = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active']);
        $project = Project::factory()->create(['creator_entity_id' => $entity->id, 'status' => 'pending_approval']);

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::TechnicalReview->value,
            'stage_order' => 1,
            'responsible_user_id' => $configuredUser->id,
        ]);

        // An in-flight approval step with the snapshot already stored
        $approvalStep = ProjectApproval::create([
            'project_id' => $project->id,
            'entity_id' => $entity->id,
            'phase' => 'technical_review',
            'technical_reviewer_id' => $snapshotUser->id,
            'status' => 'pending',
            'is_active' => true,
            'step_order' => 1,
        ]);

        $resolution = $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::TechnicalReview, $project, $approvalStep);

        // Snapshot wins
        $this->assertSame($snapshotUser->id, $resolution->user?->id);
        $this->assertSame('approval_snapshot', $resolution->source);
    }

    // ----------------------------------------------------------------
    // 7. No permission-based fallback at all
    // ----------------------------------------------------------------

    public function test_never_falls_back_to_permission_based_user_selection(): void
    {
        $entity = InternalEntity::factory()->create();

        // Users with various permissions but no EntityApprovalStage configured
        User::factory()->count(3)->create(['entity_id' => $entity->id, 'status' => 'Active']);

        $resolution = $this->resolver->resolveResponsibleUser($entity, EntityResponsibilityType::Approval);

        // Must return NoResponsibleUser, not one of the 3 users
        $this->assertNull($resolution->user);
        $this->assertSame(ResponsibilityResolution::NoResponsibleUser, $resolution->status);
    }

    // ----------------------------------------------------------------
    // 8. EntityApprovalStage duplicate is rejected by unique index
    // ----------------------------------------------------------------

    public function test_duplicate_stage_for_same_entity_is_rejected(): void
    {
        $entity = InternalEntity::factory()->create();
        $user1 = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active']);
        $user2 = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active']);

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => $user1->id,
        ]);

        $this->expectException(QueryException::class);

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => $user2->id,
        ]);
    }

    // ----------------------------------------------------------------
    // 9. assertEntityIsConfigured throws when misconfigured
    // ----------------------------------------------------------------

    public function test_assert_entity_is_configured_throws_when_stage_has_no_user(): void
    {
        $entity = InternalEntity::factory()->create();

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => null,
        ]);

        $this->expectException(ConfigurationException::class);

        $this->resolver->assertEntityIsConfigured($entity);
    }

    public function test_assert_entity_is_configured_passes_when_all_stages_valid(): void
    {
        $entity = InternalEntity::factory()->create();
        $user = User::factory()->create(['entity_id' => $entity->id, 'status' => 'Active']);

        EntityApprovalStage::create([
            'entity_id' => $entity->id,
            'stage' => EntityResponsibilityType::Approval->value,
            'stage_order' => 3,
            'responsible_user_id' => $user->id,
        ]);

        // Must not throw
        $this->resolver->assertEntityIsConfigured($entity);
        $this->assertTrue(true);
    }
}
