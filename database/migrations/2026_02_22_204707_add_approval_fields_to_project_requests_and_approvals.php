<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('project_requests', 'current_stage')) {
                $table->string('current_stage')->nullable()->after('status');
            }
            if (! Schema::hasColumn('project_requests', 'current_stage_order')) {
                $table->integer('current_stage_order')->nullable()->after('current_stage');
            }
            if (! Schema::hasColumn('project_requests', 'approval_status')) {
                $table->string('approval_status')->nullable()->after('current_stage_order');
            }
            if (! Schema::hasColumn('project_requests', 'current_approval_stage_id')) {
                $table->unsignedBigInteger('current_approval_stage_id')->nullable()->after('approval_status');
            }
        });

        Schema::table('project_approvals', function (Blueprint $table) {
            if (! Schema::hasColumn('project_approvals', 'project_request_id')) {
                $table->unsignedBigInteger('project_request_id')->nullable()->after('project_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $table->dropColumn(['current_stage', 'current_stage_order', 'approval_status', 'current_approval_stage_id']);
        });

        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropColumn('project_request_id');
        });
    }
};
