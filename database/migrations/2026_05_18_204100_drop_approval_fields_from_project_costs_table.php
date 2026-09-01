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
            $table->dropColumn(['year_type', 'approval_date_hijri', 'approval_year_gregorian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_costs', function (Blueprint $table) {
            $table->enum('year_type', ['hijri', 'gregorian'])->nullable();
            $table->date('approval_date_hijri')->nullable();
            $table->smallInteger('approval_year_gregorian')->unsigned()->nullable();
        });
    }
};
