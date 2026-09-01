<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('executive_activities', function (Blueprint $table) {
            // Add foreign keys for output and risk
            $table->foreignId('result_output_id')
                ->nullable()
                ->constrained('result_outputs')
                ->onDelete('set null');

            $table->foreignId('project_risk_id')
                ->nullable()
                ->constrained('project_risks')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('executive_activities', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['result_output_id']);
            $table->dropForeignKeyIfExists(['project_risk_id']);
            $table->dropColumn(['result_output_id', 'project_risk_id']);
        });
    }
};
