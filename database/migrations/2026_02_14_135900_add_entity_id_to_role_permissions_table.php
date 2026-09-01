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
        Schema::table('role_permission', function (Blueprint $table) {
            // Add entity_id column - NULL means applies to all entities
            $table->unsignedBigInteger('entity_id')->nullable();

            // Add foreign key constraint
            $table->foreign('entity_id')
                ->references('id')
                ->on('internal_entities')
                ->onDelete('cascade');

            // Add index for better query performance
            $table->index('entity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permission', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
            $table->dropIndex(['entity_id']);
            $table->dropColumn('entity_id');
        });
    }
};
