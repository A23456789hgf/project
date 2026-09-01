<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add entity_id column if it doesn't exist
        if (! Schema::hasColumn('project_approvals', 'entity_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('entity_id')->nullable();
            });

            // Migrate data from authority_id to entity_id
            // Handle both integer IDs and string entity names
            $approvals = DB::table('project_approvals')
                ->whereNotNull('authority_id')
                ->get();

            foreach ($approvals as $approval) {
                $entityId = null;

                // Check if authority_id is numeric (already an ID)
                if (is_numeric($approval->authority_id)) {
                    $entityId = (int) $approval->authority_id;
                } else {
                    // authority_id is a string (entity name), look up the entity
                    $entity = DB::table('internal_entities')
                        ->where('name', $approval->authority_id)
                        ->first();

                    if ($entity) {
                        $entityId = $entity->id;
                    } else {
                        // Try authorities table as fallback
                        $authority = DB::table('authorities')
                            ->where('name', $approval->authority_id)
                            ->first();

                        if ($authority) {
                            $entityId = $authority->id;
                        }
                    }
                }

                // Update the entity_id if we found a valid ID
                if ($entityId) {
                    DB::table('project_approvals')
                        ->where('id', $approval->id)
                        ->update(['entity_id' => $entityId]);
                }
            }
        }

        // Add review-related columns
        if (! Schema::hasColumn('project_approvals', 'financial_review_status')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->string('financial_review_status')->nullable();
                $table->string('technical_review_status')->nullable();
                $table->unsignedBigInteger('financial_reviewer_id')->nullable();
                $table->unsignedBigInteger('technical_reviewer_id')->nullable();
                $table->timestamp('financial_reviewed_at')->nullable();
                $table->timestamp('technical_reviewed_at')->nullable();
                $table->text('financial_notes')->nullable();
                $table->text('technical_notes')->nullable();
                $table->string('financial_attachment')->nullable();
                $table->string('technical_attachment')->nullable();
                $table->string('returned_from_stage')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->unsignedBigInteger('reviewed_by')->nullable();
            });
        }

        // Update project_activity_history to ensure it has status column
        if (Schema::hasTable('project_activity_history') && ! Schema::hasColumn('project_activity_history', 'status')) {
            Schema::table('project_activity_history', function (Blueprint $table) {
                $table->string('status')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $columns = [
                'entity_id',
                'financial_review_status',
                'technical_review_status',
                'financial_reviewer_id',
                'technical_reviewer_id',
                'financial_reviewed_at',
                'technical_reviewed_at',
                'financial_notes',
                'technical_notes',
                'financial_attachment',
                'technical_attachment',
                'returned_from_stage',
                'returned_at',
                'is_completed',
                'reviewed_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('project_approvals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('project_activity_history') && Schema::hasColumn('project_activity_history', 'status')) {
            Schema::table('project_activity_history', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
