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
        // Add entity_id column
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_id')->nullable()->after('department');

            $table->foreign('entity_id')
                ->references('id')
                ->on('internal_entities')
                ->onDelete('set null');

            $table->index('entity_id');
        });

        // Migrate existing department names to entity_id
        DB::statement('
            UPDATE users u
            INNER JOIN internal_entities ie ON u.department = ie.name
            SET u.entity_id = ie.id
            WHERE u.department IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
            $table->dropIndex(['entity_id']);
            $table->dropColumn('entity_id');
        });
    }
};
