<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->index(['created_at'], 'sug_created_at_idx');
            $table->index(['user_id'], 'sug_user_id_idx');
            $table->index(['entity_id'], 'sug_entity_id_idx');
            $table->index(['governorate_id'], 'sug_gov_id_idx');
            $table->index(['directorate_id'], 'sug_dir_id_idx');
            $table->index(['is_completed'], 'sug_is_completed_idx');
            $table->index(['deleted_at'], 'sug_deleted_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropIndex('sug_created_at_idx');
            $table->dropIndex('sug_user_id_idx');
            $table->dropIndex('sug_entity_id_idx');
            $table->dropIndex('sug_gov_id_idx');
            $table->dropIndex('sug_dir_id_idx');
            $table->dropIndex('sug_is_completed_idx');
            $table->dropIndex('sug_deleted_at_idx');
        });
    }
};
