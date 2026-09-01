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
        Schema::table('correspondences', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable()->after('project_id');
            $table->unsignedBigInteger('signed_by')->nullable()->after('task_id');
            $table->dateTime('signed_at')->nullable()->after('signed_by');
            $table->string('signature_path')->nullable()->after('signed_at');

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->foreign('signed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropForeign(['signed_by']);

            $table->dropColumn(['task_id', 'signed_by', 'signed_at', 'signature_path']);
        });
    }
};
