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
        Schema::table('objective_results', function (Blueprint $table) {
            $table->decimal('target_value', 10, 2)->nullable()->change();
            $table->string('indicator_unit')->nullable()->change();
            $table->string('indicator_type')->nullable()->change(); // Changed to string to safely allow nulls if enum gives issues
        });

        Schema::table('result_outputs', function (Blueprint $table) {
            $table->decimal('target_value', 10, 2)->nullable()->change();
            $table->string('indicator_unit')->nullable()->change();
            $table->string('indicator_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objective_results', function (Blueprint $table) {
            $table->decimal('target_value', 10, 2)->nullable(false)->change();
            $table->string('indicator_unit')->nullable(false)->change();
        });

        Schema::table('result_outputs', function (Blueprint $table) {
            $table->decimal('target_value', 10, 2)->nullable(false)->change();
            $table->string('indicator_unit')->nullable(false)->change();
        });
    }
};
