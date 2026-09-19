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
        // 1. Internal Entities: Add is_ministry_root
        if (! Schema::hasColumn('internal_entities', 'is_ministry_root')) {
            Schema::table('internal_entities', function (Blueprint $table) {
                $table->boolean('is_ministry_root')->default(false)->after('parent_id');
            });

            // Set root entity (where parent_id is null) as ministry_root
            DB::table('internal_entities')->whereNull('parent_id')->update(['is_ministry_root' => true]);
        }

        // 2. Users: Add organization_type and authority_id
        if (! Schema::hasColumn('users', 'organization_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('organization_type', 50)->nullable()->after('entity_id');
                $table->unsignedBigInteger('authority_id')->nullable()->after('organization_type');

                $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('set null');
            });

            // Data Migration for Users
            DB::table('users')->update([
                'organization_type' => 'internal',
            ]);
        }

        // 3. Projects: Add source_type and authority_id
        if (! Schema::hasColumn('projects', 'source_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('source_type', 50)->nullable()->after('internal_entity_id');
                // authority_id might already exist in projects for other reasons, let's check
            });

            if (! Schema::hasColumn('projects', 'authority_id')) {
                Schema::table('projects', function (Blueprint $table) {
                    $table->unsignedBigInteger('authority_id')->nullable()->after('source_type');
                    $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('set null');
                });
            }

            // Data Migration for Projects
            DB::table('projects')->update([
                'source_type' => 'internal',
            ]);
        }

        // 4. Project Approvals: Add approver_scope and authority_id
        if (! Schema::hasColumn('project_approvals', 'approver_scope')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->string('approver_scope', 50)->nullable()->after('entity_id');
                if (! Schema::hasColumn('project_approvals', 'authority_id')) {
                    $table->unsignedBigInteger('authority_id')->nullable()->after('approver_scope');
                    $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');
                }
            });

            // Data Migration for Project Approvals
            DB::table('project_approvals')->update([
                'approver_scope' => 'internal',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('project_approvals', 'approver_scope')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->dropForeign(['authority_id']);
                $table->dropColumn(['approver_scope', 'authority_id']);
            });
        }

        if (Schema::hasColumn('projects', 'source_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropForeign(['authority_id']);
                $table->dropColumn(['source_type', 'authority_id']);
            });
        }

        if (Schema::hasColumn('users', 'organization_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['authority_id']);
                $table->dropColumn(['organization_type', 'authority_id']);
            });
        }

        if (Schema::hasColumn('internal_entities', 'is_ministry_root')) {
            Schema::table('internal_entities', function (Blueprint $table) {
                $table->dropColumn('is_ministry_root');
            });
        }
    }
};
