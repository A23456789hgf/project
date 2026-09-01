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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('assignment_type')->default('user')->after('priority'); // 'user' or 'entity'
            $table->unsignedBigInteger('assigned_entity_id')->nullable()->after('project_entities_id');
            $table->foreign('assigned_entity_id')->references('id')->on('internal_entities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_entity_id']);
            $table->dropColumn(['assignment_type', 'assigned_entity_id']);
        });
    }
};
