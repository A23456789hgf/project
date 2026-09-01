<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify columns to be nullable for MySQL
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE project_costs MODIFY approval_date_hijri DATE NULL');
            DB::statement('ALTER TABLE project_costs MODIFY approval_year_gregorian YEAR NULL');
            DB::statement('ALTER TABLE project_costs MODIFY total_cost DECIMAL(15,2) NULL');
        } else {
            // For SQLite, use the schema builder
            Schema::table('project_costs', function (Blueprint $table) {
                $table->date('approval_date_hijri')->nullable()->change();
                $table->year('approval_year_gregorian')->nullable()->change();
                $table->decimal('total_cost', 15, 2)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('project_costs')
            ->where(function ($query) {
                $query->whereNull('approval_date_hijri')
                    ->orWhere('approval_date_hijri', '0000-00-00');
            })
            ->update(['approval_date_hijri' => DB::raw('CURRENT_DATE')]);

        DB::table('project_costs')
            ->whereNull('approval_year_gregorian')
            ->update(['approval_year_gregorian' => DB::raw('YEAR(CURRENT_DATE)')]);

        DB::table('project_costs')
            ->whereNull('total_cost')
            ->update(['total_cost' => 0]);

        DB::statement('ALTER TABLE project_costs MODIFY approval_date_hijri DATE NOT NULL');
        DB::statement('ALTER TABLE project_costs MODIFY approval_year_gregorian YEAR NOT NULL');
        DB::statement('ALTER TABLE project_costs MODIFY total_cost DECIMAL(15,2) NOT NULL');
    }
};
