<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Drops the priority_id foreign key and column from plans table
     * - Adds start_date_g, start_date_h, end_date_g, end_date_h, duration columns
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Drop the foreign key constraint first, then the column
            $table->dropForeign(['priority_id']);
            $table->dropColumn('priority_id');

            // Add the new date and duration fields
            $table->date('start_date_g')->nullable()->after('submitting_entity_id')->comment('تاريخ البداية (ميلادي)');
            $table->string('start_date_h', 20)->nullable()->after('start_date_g')->comment('تاريخ البداية (هجري)');
            $table->date('end_date_g')->nullable()->after('start_date_h')->comment('تاريخ النهاية (ميلادي)');
            $table->string('end_date_h', 20)->nullable()->after('end_date_g')->comment('تاريخ النهاية (هجري)');
            $table->unsignedSmallInteger('duration')->nullable()->after('end_date_h')->comment('المدة بالأيام (محسوبة تلقائياً)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Remove the new columns
            $table->dropColumn(['start_date_g', 'start_date_h', 'end_date_g', 'end_date_h', 'duration']);

            // Re-add priority_id
            $table->foreignId('priority_id')->after('id')->constrained('priorities');
        });
    }
};
