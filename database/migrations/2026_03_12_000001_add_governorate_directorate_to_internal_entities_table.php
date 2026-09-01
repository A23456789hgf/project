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
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('authority_id');
            $table->unsignedBigInteger('directorate_id')->nullable()->after('governorate_id');

            $table->foreign('governorate_id')
                ->references('id')
                ->on('governorates')
                ->onDelete('set null');

            $table->foreign('directorate_id')
                ->references('id')
                ->on('directorates')
                ->onDelete('set null');
        });

        // Back-fill existing rows from the linked authority
        DB::statement('
            UPDATE internal_entities ie
            INNER JOIN authorities a ON ie.authority_id = a.id
            SET
                ie.governorate_id = a.governorate_id,
                ie.directorate_id = a.directorate_id
            WHERE ie.authority_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['directorate_id']);
            $table->dropColumn(['governorate_id', 'directorate_id']);
        });
    }
};
