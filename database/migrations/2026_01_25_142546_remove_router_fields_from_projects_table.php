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
            // Drop foreign keys first
            $table->dropForeign(['main_router_id']);
            $table->dropForeign(['sub_router_id']);

            // Drop columns
            $table->dropColumn(['main_router_id', 'sub_router_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('main_router_id')->nullable()->after('program_id');
            $table->unsignedBigInteger('sub_router_id')->nullable()->after('main_router_id');

            $table->foreign('main_router_id')->references('id')->on('main_routers')->onDelete('set null');
            $table->foreign('sub_router_id')->references('id')->on('sub_routers')->onDelete('set null');
        });
    }
};
