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
            if (! Schema::hasColumn('projects', 'created_by_entity')) {
                $table->string('created_by_entity')->nullable()->after('created_by_user_id');
            }
            if (! Schema::hasColumn('projects', 'updated_by_user_id')) {
                $table->unsignedBigInteger('updated_by_user_id')->nullable()->after('created_by_entity');
                $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('projects', 'updated_by_entity')) {
                $table->string('updated_by_entity')->nullable()->after('updated_by_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn(['created_by_entity', 'updated_by_user_id', 'updated_by_entity']);
        });
    }
};
