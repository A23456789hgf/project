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
        $tables = [
            'programs', 'domains', 'subdomains', 'interventions',
            'target_categories', 'priorities', 'correspondence_replies',
            'correspondence_referrals', 'correspondence_forwardings',
            'correspondence_movement_logs', 'correspondence_movements',
            'correspondence_activities',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                // Fix MySQL 1067 Invalid default value for existing timestamp columns
                if ($tableName === 'correspondence_referrals' && Schema::hasColumn($tableName, 'referred_at')) {
                    DB::statement("ALTER TABLE {$tableName} MODIFY referred_at TIMESTAMP NULL");
                }
                if ($tableName === 'correspondence_replies' && Schema::hasColumn($tableName, 'replied_at')) {
                    DB::statement("ALTER TABLE {$tableName} MODIFY replied_at TIMESTAMP NULL");
                }

                // Check if columns exist using Schema to avoid errors
                if (! Schema::hasColumn($tableName, 'geographic_scope_id')) {
                    DB::statement("ALTER TABLE {$tableName} ADD geographic_scope_id BIGINT NULL");
                }
                if (! Schema::hasColumn($tableName, 'administrative_scope_id')) {
                    DB::statement("ALTER TABLE {$tableName} ADD administrative_scope_id BIGINT NULL");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'programs',
            'domains',
            'subdomains',
            'interventions',
            'target_categories',
            'priorities',
            'correspondence_replies',
            'correspondence_referrals',
            'correspondence_forwardings',
            'correspondence_movement_logs',
            'correspondence_movements',
            'correspondence_activities',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $cols = [];
                    if (Schema::hasColumn($tableName, 'geographic_scope_id')) {
                        $cols[] = 'geographic_scope_id';
                    }
                    if (Schema::hasColumn($tableName, 'administrative_scope_id')) {
                        $cols[] = 'administrative_scope_id';
                    }

                    if (! empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });
            }
        }
    }
};
