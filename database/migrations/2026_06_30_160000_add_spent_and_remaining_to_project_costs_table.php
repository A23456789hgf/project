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
        Schema::table('project_costs', function (Blueprint $table) {
            if (! Schema::hasColumn('project_costs', 'spent_amount')) {
                $table->decimal('spent_amount', 15, 2)->nullable()->after('total_cost');
            }
            if (! Schema::hasColumn('project_costs', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->nullable()->after('spent_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_costs', function (Blueprint $table) {
            if (Schema::hasColumn('project_costs', 'spent_amount')) {
                $table->dropColumn('spent_amount');
            }
            if (Schema::hasColumn('project_costs', 'remaining_amount')) {
                $table->dropColumn('remaining_amount');
            }
        });
    }
};
