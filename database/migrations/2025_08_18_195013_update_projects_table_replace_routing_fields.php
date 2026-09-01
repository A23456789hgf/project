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
        Schema::table('projects', function (Blueprint $table) {
            // Add sub_router_id foreign key
            $table->foreignId('sub_router_id')
                ->nullable()
                ->constrained('sub_routers')
                ->onDelete('cascade')
                ->after('main_router_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['sub_router_id']);

            // Drop column
            $table->dropColumn('sub_router_id');
        });
    }
};
