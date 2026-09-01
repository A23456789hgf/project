<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change approval_year_gregorian from YEAR (1901-2155) to SMALLINT UNSIGNED (0-65535)
     * so it can hold Hijri years like 1447 which are below the MySQL YEAR minimum of 1901.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE project_costs MODIFY approval_year_gregorian SMALLINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First clamp any out-of-range values before reverting to YEAR type
        DB::statement('UPDATE project_costs SET approval_year_gregorian = NULL WHERE approval_year_gregorian < 1901 OR approval_year_gregorian > 2155');
        DB::statement('ALTER TABLE project_costs MODIFY approval_year_gregorian YEAR NULL');
    }
};
