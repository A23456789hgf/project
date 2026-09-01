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
        Schema::table('projects', function (Blueprint $table) {
            $table->index('created_by_entity');
            $table->index('updated_by_entity');
            $table->index(['status', 'created_by_entity']);
        });

        Schema::table('correspondences', function (Blueprint $table) {
            $table->index('priority');
            $table->index(['status', 'recipient_entity_id']);
            $table->index(['status', 'sender_entity_id']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['created_by_entity']);
            $table->dropIndex(['updated_by_entity']);
            $table->dropIndex(['status', 'created_by_entity']);
        });

        Schema::table('correspondences', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['status', 'recipient_entity_id']);
            $table->dropIndex(['status', 'sender_entity_id']);
        });
    }
};
