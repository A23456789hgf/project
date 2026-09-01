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
            // Drop the existing foreign key constraint
            // We use the array syntax which Laravel converts to the default index name
            // projects_current_approval_stage_id_foreign
            $table->dropForeign(['current_approval_stage_id']);

            // We also need to drop the index probably, but dropForeign might not drop the index.
            // Usually foreign keys create an index. We can keep the index if we just change the constraint.

            // Add the new foreign key constraint referencing 'stages' table
            $table->foreign('current_approval_stage_id')
                ->references('id')
                ->on('stages')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['current_approval_stage_id']);

            // Restore the original foreign key to 'approval_stages'
            $table->foreign('current_approval_stage_id')
                ->references('id')
                ->on('approval_stages')
                ->onDelete('set null');
        });
    }
};
