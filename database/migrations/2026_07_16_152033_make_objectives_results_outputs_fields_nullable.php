<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE special_objectives MODIFY measurement_unit VARCHAR(191) NULL;');
        DB::statement("ALTER TABLE objective_results MODIFY target_value DECIMAL(10,2) NULL, MODIFY indicator_type ENUM('quantitative','relative','qualitative') NULL, MODIFY indicator_unit VARCHAR(191) NULL;");
        DB::statement("ALTER TABLE result_outputs MODIFY target_value DECIMAL(10,2) NULL, MODIFY indicator_type ENUM('quantitative','relative','qualitative') NULL, MODIFY indicator_unit VARCHAR(191) NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For down, we keep them nullable or change them back to NOT NULL, but it's safer to just leave them nullable or set defaults.
        // I will just leave it empty or revert to NOT NULL.
        DB::statement('ALTER TABLE special_objectives MODIFY measurement_unit VARCHAR(191) NOT NULL;');
        DB::statement("ALTER TABLE objective_results MODIFY target_value DECIMAL(10,2) NOT NULL, MODIFY indicator_type ENUM('quantitative','relative','qualitative') NOT NULL DEFAULT 'quantitative', MODIFY indicator_unit VARCHAR(191) NOT NULL;");
        DB::statement("ALTER TABLE result_outputs MODIFY target_value DECIMAL(10,2) NOT NULL, MODIFY indicator_type ENUM('quantitative','relative','qualitative') NOT NULL DEFAULT 'quantitative', MODIFY indicator_unit VARCHAR(191) NOT NULL;");
    }
};
