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
            $table->text('objective')->change();
        });

        Schema::table('special_objectives', function (Blueprint $table) {
            $table->text('objective')->change();
        });

        if (Schema::hasTable('plan_project_goals')) {
            Schema::table('plan_project_goals', function (Blueprint $table) {
                $table->text('specific_goal')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_objectives', function (Blueprint $table) {
            $table->string('objective')->change();
        });

        Schema::table('special_objectives', function (Blueprint $table) {
            $table->string('objective')->change();
        });

        if (Schema::hasTable('plan_project_goals')) {
            Schema::table('plan_project_goals', function (Blueprint $table) {
                $table->string('specific_goal')->change();
            });
        }
    }
};
