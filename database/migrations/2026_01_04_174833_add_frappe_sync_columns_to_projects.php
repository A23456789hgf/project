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
            $table->boolean('frappe_synced')->default(false)->after('approval_status');
            $table->dateTime('frappe_sync_at')->nullable()->after('frappe_synced');
            $table->integer('frappe_sync_attempts')->default(0)->after('frappe_sync_at');
            $table->string('frappe_project_id')->nullable()->after('frappe_sync_attempts');
            $table->text('frappe_sync_error')->nullable()->after('frappe_project_id');
            $table->dateTime('frappe_last_sync_attempt')->nullable()->after('frappe_sync_error');

            $table->index(['status', 'frappe_synced']);
            $table->index(['frappe_sync_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status', 'frappe_synced']);
            $table->dropIndex(['frappe_sync_at']);
            $table->dropColumn([
                'frappe_synced',
                'frappe_sync_at',
                'frappe_sync_attempts',
                'frappe_project_id',
                'frappe_sync_error',
                'frappe_last_sync_attempt',
            ]);
        });
    }
};
