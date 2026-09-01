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
        Schema::table('project_approvals', function (Blueprint $table) {
            // Drop the composite index involving authority_id
            $table->dropIndex(['project_id', 'authority_id']);

            // Drop the foreign key
            // Laravel convention: table_column_foreign
            $table->dropForeign(['authority_id']);
        });

        Schema::table('project_approvals', function (Blueprint $table) {
            // Change the column to string
            $table->string('authority_id', 191)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot easily revert string data to integer if it contains non-integers.
        // This down migration is destructive or best-effort.
        Schema::table('project_approvals', function (Blueprint $table) {
            // We assume data is clean or truncated for rollback
            // $table->unsignedBigInteger('authority_id')->change();
            // $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');
            // $table->index(['project_id', 'authority_id']);
        });
    }
};
