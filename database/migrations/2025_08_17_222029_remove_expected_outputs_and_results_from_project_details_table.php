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
            // Remove expected_outputs and expected_results columns
            $table->dropColumn(['expected_outputs', 'expected_results']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_details', function (Blueprint $table) {
            // Add back the columns if migration is rolled back
            $table->text('expected_results')->nullable();
            $table->text('expected_outputs')->nullable();
        });
    }
};
