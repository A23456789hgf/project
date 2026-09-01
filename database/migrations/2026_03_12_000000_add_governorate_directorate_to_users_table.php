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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('entity_id');
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

        // Back-fill existing users: derive governorate_id / directorate_id from entity → authority
        DB::statement('
            UPDATE users u
            INNER JOIN internal_entities ie ON u.entity_id = ie.id
            INNER JOIN authorities a ON ie.authority_id = a.id
            SET
                u.governorate_id  = a.governorate_id,
                u.directorate_id  = a.directorate_id
            WHERE u.entity_id IS NOT NULL
              AND ie.authority_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['directorate_id']);
            $table->dropColumn(['governorate_id', 'directorate_id']);
        });
    }
};
