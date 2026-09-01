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
            if (! Schema::hasColumn('project_requests', 'main_router_id')) {
                $table->unsignedBigInteger('main_router_id')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'sub_router_id')) {
                $table->unsignedBigInteger('sub_router_id')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'priority')) {
                $table->string('priority')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'target_categories')) {
                $table->string('target_categories')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'draft_saved_at')) {
                $table->timestamp('draft_saved_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $columns = ['main_router_id', 'sub_router_id', 'priority', 'target_categories', 'draft_saved_at'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('project_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
