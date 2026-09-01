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
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('last_saved_step')->nullable()->default(1)->change();
            $table->string('approval_status')->nullable()->default('pending')->change();
            $table->boolean('frappe_synced')->nullable()->default(false)->change();
            $table->integer('frappe_sync_attempts')->nullable()->default(0)->change();
            $table->integer('current_stage_order')->nullable()->default(1)->change();
        });

        // Use raw SQL for enum because doctrine/dbal throws "Unknown column type 'enum'" exception
        DB::statement("ALTER TABLE projects MODIFY internal_review_status ENUM('pending', 'approved', 'rejected') NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('last_saved_step')->nullable(false)->default(1)->change();
            $table->string('approval_status')->nullable(false)->default('pending')->change();
            $table->boolean('frappe_synced')->nullable(false)->default(false)->change();
            $table->integer('frappe_sync_attempts')->nullable(false)->default(0)->change();
            $table->integer('current_stage_order')->nullable(false)->default(1)->change();
        });

        DB::statement("ALTER TABLE projects MODIFY internal_review_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
