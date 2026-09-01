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
        Schema::table('main_objectives', function (Blueprint $table) {
            $table->string('indicator')->nullable()->change();
            $table->string('indicator_unit')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_objectives', function (Blueprint $table) {
            $table->string('indicator')->nullable(false)->change();
            $table->string('indicator_unit')->nullable(false)->change();
        });
    }
};
