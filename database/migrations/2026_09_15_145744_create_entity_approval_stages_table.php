<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates entity_approval_stages table – the single source of truth for:
     *   - Which approval stages are enabled per entity
     *   - The order of those stages
     *   - The explicitly configured responsible user for each stage
     *
     * Stage values: TECHNICAL_REVIEW, FINANCIAL_REVIEW, APPROVAL
     * Fixed logical order: TECHNICAL(1) → FINANCIAL(2) → APPROVAL(3)
     * Only enabled stages produce ProjectApproval records.
     */
    public function up(): void
    {
        Schema::create('entity_approval_stages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('entity_id')
                ->constrained('internal_entities')
                ->cascadeOnDelete()
                ->comment('The entity this stage belongs to');

            $table->string('stage', 30)
                ->comment('Stage type: TECHNICAL_REVIEW | FINANCIAL_REVIEW | APPROVAL');

            $table->unsignedTinyInteger('stage_order')
                ->default(1)
                ->comment('Fixed order: TECHNICAL_REVIEW=1, FINANCIAL_REVIEW=2, APPROVAL=3');

            $table->foreignId('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Explicitly configured responsible user (snapshot into ProjectApproval on workflow creation)');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Prevent duplicate stage configuration for the same entity
            $table->unique(['entity_id', 'stage'], 'entity_stage_unique');

            // Index for workflow generation lookups
            $table->index(['entity_id', 'stage_order'], 'entity_stage_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_approval_stages');
    }
};
