<?php

use App\Enums\EntityResponsibilityType;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure all existing entities have default stages if they don't have any configured yet.
     */
    public function up(): void
    {
        // Safe deployment step to backfill default stages for existing entities.
        // It does not duplicate, modify existing configurations, or overwrite responsible users.

        $entities = InternalEntity::withoutGlobalScopes()->get();
        $stageTypes = EntityResponsibilityType::orderedCases();

        foreach ($entities as $entity) {
            // Check if the entity already has ANY configured stages.
            // If it does, we skip it completely to avoid messing with customized workflows.
            if (EntityApprovalStage::where('entity_id', $entity->id)->exists()) {
                continue;
            }

            foreach ($stageTypes as $stageType) {
                EntityApprovalStage::firstOrCreate(
                    [
                        'entity_id' => $entity->id,
                        'stage' => $stageType->value,
                    ],
                    [
                        'stage_order' => $stageType->stageOrder(),
                        'created_by' => null, // Leave null since this is a system backfill
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as we don't know which ones were added by this migration vs manually added.
    }
};
