<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->after('project_id', function (Blueprint $table) {
                $table->foreignId('stage_id')->nullable()->constrained('stages')->onDelete('restrict');
                $table->foreignId('stage_status_id')->nullable()->constrained('stage_statuses')->onDelete('restrict');
            });
        });
    }

    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['stage_id', 'stage_status_id']);
            $table->dropColumn(['stage_id', 'stage_status_id']);
        });
    }
};
