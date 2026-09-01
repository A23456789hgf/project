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
        if (Schema::hasTable('preliminary_procedures')) {
            Schema::table('preliminary_procedures', function (Blueprint $table) {
                if (! Schema::hasColumn('preliminary_procedures', 'project_entities_id')) {
                    $table->unsignedBigInteger('project_entities_id')->nullable()->after('activity_id');
                    $table->foreign('project_entities_id')
                        ->references('id')
                        ->on('project_entities')
                        ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('preliminary_procedures')) {
            Schema::table('preliminary_procedures', function (Blueprint $table) {
                if (Schema::hasColumn('preliminary_procedures', 'project_entities_id')) {
                    $table->dropForeign(['project_entities_id']);
                    $table->dropColumn('project_entities_id');
                }
            });
        }
    }
};
