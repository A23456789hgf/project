<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add request_number to projects table so the original
     * QPROYYYY#### request number is retained after transfer.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('request_number')->nullable()->after('form_number')
                ->comment('Original QPROYYYY#### request number from the project request');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
    }
};
