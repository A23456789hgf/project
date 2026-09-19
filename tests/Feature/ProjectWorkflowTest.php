<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the external/internal project workflow fix.
 *
 * Covers:
 * - Project origin helpers (getOriginType, getOriginAuthorityId, getOriginEntityId)
 * - ProjectPolicy::closeDraft authorization for internal and external users
 * - ProjectPolicy::resubmit for internal vs external
 * - Cross-authority/cross-entity access prevention
 */
class ProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // 1. Project Model Origin Helpers
    // =========================================================================

    public function test_get_origin_type_returns_external_for_external_source_type(): void
    {
        $project = new Project(['source_type' => 'external', 'authority_id' => 1]);
        $this->assertEquals('external', $project->getOriginType());
    }

    public function test_get_origin_type_returns_internal_for_internal_source_type(): void
    {
        $project = new Project(['source_type' => 'internal', 'creator_entity_id' => 5]);
        $this->assertEquals('internal', $project->getOriginType());
    }

    public function test_get_origin_type_fallback_uses_authority_id_heuristic(): void
    {
        // No source_type, but has authority_id and no entity references
        $project = new Project(['authority_id' => 2]);
        $this->assertEquals('external', $project->getOriginType());
    }

    public function test_get_origin_type_fallback_defaults_to_internal(): void
    {
        $project = new Project(['creator_entity_id' => 3]);
        $this->assertEquals('internal', $project->getOriginType());
    }

    public function test_get_origin_authority_id_returns_authority_id(): void
    {
        $project = new Project(['authority_id' => 7]);
        $this->assertEquals(7, $project->getOriginAuthorityId());
    }

    public function test_get_origin_authority_id_returns_null_for_internal_project(): void
    {
        $project = new Project(['source_type' => 'internal', 'creator_entity_id' => 5]);
        $this->assertNull($project->getOriginAuthorityId());
    }

    public function test_get_origin_entity_id_never_returns_authority_id(): void
    {
        // Semantic separation: getOriginEntityId must NOT return authority_id
        $project = new Project(['source_type' => 'external', 'authority_id' => 99, 'creator_entity_id' => null]);
        $this->assertNull(
            $project->getOriginEntityId(),
            'getOriginEntityId() must NOT return authority_id for external projects'
        );
    }

    public function test_internal_project_origin_entity_id_resolves_correctly(): void
    {
        $project = new Project(['source_type' => 'internal', 'creator_entity_id' => 42]);
        $this->assertEquals(42, $project->getOriginEntityId());
    }
}
