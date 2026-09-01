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
        if (Schema::hasTable('authorities') && ! Schema::hasColumn('authorities', 'financing_type_id')) {
            Schema::table('authorities', function (Blueprint $table) {
                $table->unsignedBigInteger('financing_type_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('authorities') && Schema::hasColumn('authorities', 'financing_type_id')) {
            Schema::table('authorities', function (Blueprint $table) {
                $table->dropColumn('financing_type_id');
            });
        }
    }
};
