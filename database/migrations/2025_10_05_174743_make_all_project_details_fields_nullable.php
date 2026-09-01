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
        Schema::table('project_details', function (Blueprint $table) {
            $table->text('project_summary')->nullable()->change();
            $table->text('problem_and_justification')->nullable()->change();
            $table->text('expected_impact')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->text('project_summary')->nullable(false)->change();
            $table->text('problem_and_justification')->nullable(false)->change();
            $table->text('expected_impact')->nullable(false)->change();
        });
    }
};
