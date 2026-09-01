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
        Schema::table('entity_officers', function (Blueprint $table) {
            if (! Schema::hasColumn('entity_officers', 'creator_username')) {
                $table->string('creator_username')->nullable()->after('created_at');
            }
            if (! Schema::hasColumn('entity_officers', 'creator_entity_id')) {
                $table->unsignedBigInteger('creator_entity_id')->nullable()->after('creator_username');
                $table->foreign('creator_entity_id')->references('id')->on('internal_entities')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_officers', function (Blueprint $table) {
            $table->dropForeign(['creator_entity_id']);
            $table->dropColumn(['creator_username', 'creator_entity_id']);
        });
    }
};
