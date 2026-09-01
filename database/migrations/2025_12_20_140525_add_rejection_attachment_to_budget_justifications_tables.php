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
        Schema::table('procedure_budget_justifications', function (Blueprint $table) {
            $table->string('rejection_attachment')->nullable()->after('reviewer_notes');
        });

        Schema::table('execution_budget_justifications', function (Blueprint $table) {
            $table->string('rejection_attachment')->nullable()->after('reviewer_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_budget_justifications', function (Blueprint $table) {
            $table->dropColumn('rejection_attachment');
        });

        Schema::table('execution_budget_justifications', function (Blueprint $table) {
            $table->dropColumn('rejection_attachment');
        });
    }
};
