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
        Schema::table('task_memos', function (Blueprint $table) {
            $table->unsignedBigInteger('correspondence_id')->nullable()->after('task_id');
            $table->foreign('correspondence_id')->references('id')->on('correspondences')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_memos', function (Blueprint $table) {
            $table->dropForeign(['correspondence_id']);
            $table->dropColumn('correspondence_id');
        });
    }
};
