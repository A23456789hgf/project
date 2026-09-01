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
        Schema::table('authorities', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('parent_id');
            $table->unsignedBigInteger('directorate_id')->nullable()->after('governorate_id');

            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('directorate_id')->references('id')->on('directorates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authorities', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['directorate_id']);
            $table->dropColumn(['governorate_id', 'directorate_id']);
        });
    }
};
