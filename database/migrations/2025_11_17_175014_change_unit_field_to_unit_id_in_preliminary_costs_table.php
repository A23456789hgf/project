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
        Schema::table('preliminary_costs', function (Blueprint $table) {
            // Drop the existing unit column
            $table->dropColumn('unit');

            // Add unit_id foreign key
            $table->foreignId('unit_id')->nullable()->after('financial_item_id');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preliminary_costs', function (Blueprint $table) {
            // Drop the foreign key and unit_id column
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');

            // Recreate the unit column
            $table->string('unit')->nullable()->after('financial_item_id');
        });
    }
};
