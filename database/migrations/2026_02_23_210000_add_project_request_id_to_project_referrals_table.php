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
        Schema::table('project_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('project_referrals', 'project_request_id')) {
                $table->foreignId('project_request_id')
                    ->nullable()
                    ->after('project_id')
                    ->constrained('project_requests')
                    ->onDelete('cascade');
            }

            // Allow project_id to be nullable if it's not already
            $table->foreignId('project_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_referrals', function (Blueprint $table) {
            $table->dropForeign(['project_request_id']);
            $table->dropColumn('project_request_id');

            // Revert project_id to NOT nullable if that was its original state
            // $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
