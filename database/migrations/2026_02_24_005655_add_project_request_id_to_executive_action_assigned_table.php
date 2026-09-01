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
        Schema::table('executive_action_assigned', function (Blueprint $table) {
            // 1. Make project_id nullable
            if (Schema::hasColumn('executive_action_assigned', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->change();
            }

            // 2. Add project_request_id if it doesn't exist
            if (! Schema::hasColumn('executive_action_assigned', 'project_request_id')) {
                $afterColumn = Schema::hasColumn('executive_action_assigned', 'project_id') ? 'project_id' : 'id';
                $table->unsignedBigInteger('project_request_id')->nullable()->after($afterColumn);
                $table->index('project_request_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('executive_action_assigned', function (Blueprint $table) {
            if (Schema::hasColumn('executive_action_assigned', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('executive_action_assigned', 'project_request_id')) {
                $table->dropIndex(['project_request_id']);
                $table->dropColumn('project_request_id');
            }
        });
    }
};
