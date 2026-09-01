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
        Schema::table('users', function (Blueprint $table) {
            // Check if the column exists to avoid errors if it was previously created
            if (! Schema::hasColumn('users', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->foreign('entity_id')->references('id')->on('entities');
            } else {
                // If the column exists but we just need to update the foreign key
                // Note: updating foreign keys might require dropping the old one first
                // For safety, we only add the column if it doesn't exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'entity_id')) {
                $table->dropForeign(['entity_id']);
                $table->dropColumn('entity_id');
            }
        });
    }
};
