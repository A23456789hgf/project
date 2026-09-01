<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function addIndexSafely(string $table, array|string $columns, ?string $indexName = null): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $cols = (array) $columns;
        foreach ($cols as $col) {
            if (! Schema::hasColumn($table, $col)) {
                return;
            }
        }

        $name = $indexName ?? ($table.'_'.implode('_', $cols).'_idx');

        try {
            Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                $t->index($cols, $name);
            });
        } catch (Throwable $e) {
            // Index already exists or cannot be created, ignore safely
        }
    }

    public function up(): void
    {
        $this->addIndexSafely('projects', 'project_name', 'idx_projects_project_name');
        $this->addIndexSafely('projects', 'created_at', 'idx_projects_created_at');
        $this->addIndexSafely('project_entities', 'entity_name', 'idx_project_entities_entity_name');
        $this->addIndexSafely('audit_logs', ['created_at', 'user_id'], 'idx_audit_logs_created_user');
    }

    public function down(): void
    {
        // Safe no-op
    }
};
