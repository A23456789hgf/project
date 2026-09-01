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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'creator_username')) {
                $table->string('creator_username')->nullable()->after('created_at');
            }
            if (! Schema::hasColumn('users', 'creator_entity_id')) {
                $table->unsignedBigInteger('creator_entity_id')->nullable()->after('creator_username');
                // We use internal_entities here as that's what the original schema expected,
                // but you can change it to entities if needed.
                $table->foreign('creator_entity_id')->references('id')->on('internal_entities')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'creator_entity_id')) {
                $table->dropForeign(['creator_entity_id']);
                $table->dropColumn('creator_entity_id');
            }
            if (Schema::hasColumn('users', 'creator_username')) {
                $table->dropColumn('creator_username');
            }
        });
    }
};
