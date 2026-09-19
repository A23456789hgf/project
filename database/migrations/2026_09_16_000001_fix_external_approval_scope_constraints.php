<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إصلاح بنية الاعتمادات لدعم الجهات الداخلية والخارجية.
     *
     * القاعدة المطلوبة:
     *
     * Internal Approval:
     *   approver_scope = internal
     *   entity_id       = NOT NULL
     *   authority_id    = NULL
     *
     * External Approval:
     *   approver_scope = external
     *   entity_id       = NULL
     *   authority_id    = NOT NULL
     */
    public function up(): void
    {
        /*
         * ================================================================
         * 1. PROJECT APPROVALS
         * ================================================================
         */

        if (! Schema::hasTable('project_approvals')) {
            throw new RuntimeException(
                'جدول project_approvals غير موجود. لا يمكن تطبيق إصلاح مسار الاعتمادات.'
            );
        }

        /*
         * approver_scope
         */
        if (! Schema::hasColumn('project_approvals', 'approver_scope')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->string('approver_scope', 50)
                    ->nullable()
                    ->after('entity_id');
            });
        }

        /*
         * authority_id
         */
        if (! Schema::hasColumn('project_approvals', 'authority_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('authority_id')
                    ->nullable()
                    ->after('approver_scope');
            });

            /*
             * إضافة FK في خطوة مستقلة.
             */
            try {
                Schema::table('project_approvals', function (Blueprint $table) {
                    $table->foreign('authority_id')
                        ->references('id')
                        ->on('authorities')
                        ->nullOnDelete();
                });
            } catch (Throwable $e) {
                Log::warning(
                    'Could not create project_approvals.authority_id foreign key: '
                    .$e->getMessage()
                );
            }
        }

        /*
         * ================================================================
         * 2. DATA NORMALIZATION
         * ================================================================
         *
         * السجلات القديمة تعتبر داخلية إذا لم يكن لها authority_id.
         */

        DB::table('project_approvals')
            ->whereNull('approver_scope')
            ->whereNull('authority_id')
            ->update([
                'approver_scope' => 'internal',
            ]);

        /*
         * إذا كان السجل يحتوي authority_id
         * فهو اعتماد خارجي.
         */
        DB::table('project_approvals')
            ->whereNotNull('authority_id')
            ->update([
                'approver_scope' => 'external',
            ]);

        /*
         * تنظيف القيم النصية.
         */
        DB::table('project_approvals')
            ->whereRaw('LOWER(approver_scope) = ?', ['internal'])
            ->update([
                'approver_scope' => 'internal',
            ]);

        DB::table('project_approvals')
            ->whereRaw('LOWER(approver_scope) = ?', ['external'])
            ->update([
                'approver_scope' => 'external',
            ]);

        /*
         * ================================================================
         * 3. EXTERNAL RECORDS MUST NOT REFERENCE INTERNAL ENTITY
         * ================================================================
         */

        DB::table('project_approvals')
            ->where('approver_scope', 'external')
            ->whereNotNull('authority_id')
            ->update([
                'entity_id' => null,
            ]);

        /*
         * ================================================================
         * 4. INTERNAL RECORDS MUST NOT REFERENCE EXTERNAL AUTHORITY
         * ================================================================
         */

        DB::table('project_approvals')
            ->where('approver_scope', 'internal')
            ->update([
                'authority_id' => null,
            ]);

        /*
         * ================================================================
         * 5. CRITICAL FIX:
         *
         * project_approvals.entity_id MUST BE NULLABLE.
         *
         * External approval steps intentionally have:
         *
         * entity_id = NULL
         * authority_id = <authority>
         * ================================================================
         */

        if (Schema::hasColumn('project_approvals', 'entity_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('entity_id')
                    ->nullable()
                    ->change();
            });
        }

        /*
         * ================================================================
         * 6. INDEXES
         * ================================================================
         *
         * هذه الفهارس مهمة عند البحث عن:
         *
         * - خطوات جهة داخلية
         * - خطوات جهة خارجية
         * - الخطوة النشطة
         * ================================================================
         */

        $this->addIndexIfMissing(
            'project_approvals',
            ['approver_scope', 'entity_id'],
            'project_approvals_scope_entity_idx'
        );

        $this->addIndexIfMissing(
            'project_approvals',
            ['approver_scope', 'authority_id'],
            'project_approvals_scope_authority_idx'
        );

        $this->addIndexIfMissing(
            'project_approvals',
            ['project_id', 'is_active'],
            'project_approvals_project_active_idx'
        );

        $this->addIndexIfMissing(
            'project_approvals',
            ['project_id', 'step_order'],
            'project_approvals_project_step_idx'
        );

        /*
         * ================================================================
         * 7. USERS
         * ================================================================
         */

        if (Schema::hasTable('users')) {
            /*
             * تصحيح المستخدمين الذين لديهم authority_id.
             */
            if (
                Schema::hasColumn('users', 'organization_type')
                && Schema::hasColumn('users', 'authority_id')
            ) {
                DB::table('users')
                    ->whereNotNull('authority_id')
                    ->update([
                        'organization_type' => 'external',
                        'entity_id' => null,
                    ]);
            }

            /*
             * المستخدم الداخلي.
             */
            if (
                Schema::hasColumn('users', 'organization_type')
                && Schema::hasColumn('users', 'entity_id')
            ) {
                DB::table('users')
                    ->whereNotNull('entity_id')
                    ->whereNull('authority_id')
                    ->update([
                        'organization_type' => 'internal',
                    ]);
            }
        }

        /*
         * ================================================================
         * 8. PROJECTS
         * ================================================================
         */

        if (
            Schema::hasTable('projects')
            && Schema::hasColumn('projects', 'source_type')
            && Schema::hasColumn('projects', 'authority_id')
        ) {
            /*
             * مشروع يحتوي authority_id
             * => External
             */
            DB::table('projects')
                ->whereNotNull('authority_id')
                ->update([
                    'source_type' => 'external',
                ]);

            /*
             * مشروع لا يحتوي authority_id
             * ولم يتم تحديد مصدره.
             * => Internal
             */
            DB::table('projects')
                ->whereNull('authority_id')
                ->whereNull('source_type')
                ->update([
                    'source_type' => 'internal',
                ]);
        }

        /*
         * ================================================================
         * 9. VALIDATE MINISTRY ROOT
         * ================================================================
         */

        if (
            Schema::hasTable('internal_entities')
            && Schema::hasColumn(
                'internal_entities',
                'is_ministry_root'
            )
        ) {
            $rootCount = DB::table('internal_entities')
                ->where('is_ministry_root', true)
                ->count();

            if ($rootCount === 0) {
                Log::warning(
                    'No Ministry Root is configured. '
                    .'Approval chains will not be able to reach final execution.'
                );
            }

            if ($rootCount > 1) {
                Log::warning(
                    "Multiple Ministry Roots detected ({$rootCount}). "
                    .'Exactly one Ministry Root must be configured.'
                );
            }
        }

        /*
         * ================================================================
         * 10. VALIDATE APPROVAL DATA
         * ================================================================
         */

        $invalidInternalCount = DB::table('project_approvals')
            ->where('approver_scope', 'internal')
            ->whereNull('entity_id')
            ->count();

        if ($invalidInternalCount > 0) {
            Log::warning(
                "Found {$invalidInternalCount} internal project approval records without entity_id."
            );
        }

        $invalidExternalCount = DB::table('project_approvals')
            ->where('approver_scope', 'external')
            ->whereNull('authority_id')
            ->count();

        if ($invalidExternalCount > 0) {
            Log::warning(
                "Found {$invalidExternalCount} external project approval records without authority_id."
            );
        }

        $unknownScopeCount = DB::table('project_approvals')
            ->whereNotNull('approver_scope')
            ->whereNotIn(
                'approver_scope',
                ['internal', 'external']
            )
            ->count();

        if ($unknownScopeCount > 0) {
            Log::warning(
                "Found {$unknownScopeCount} project approval records with invalid approver_scope."
            );
        }
    }

    /**
     * Reverse only the changes that are safe to reverse.
     *
     * Important:
     * We DO NOT remove authority_id or approver_scope here because
     * they may contain production workflow data.
     */
    public function down(): void
    {
        if (! Schema::hasTable('project_approvals')) {
            return;
        }

        /*
         * Remove only indexes created by this migration.
         */
        $this->dropIndexIfExists(
            'project_approvals',
            'project_approvals_scope_entity_idx'
        );

        $this->dropIndexIfExists(
            'project_approvals',
            'project_approvals_scope_authority_idx'
        );

        $this->dropIndexIfExists(
            'project_approvals',
            'project_approvals_project_active_idx'
        );

        $this->dropIndexIfExists(
            'project_approvals',
            'project_approvals_project_step_idx'
        );

        /*
         * We intentionally do NOT change entity_id
         * back to NOT NULL.
         *
         * Doing so could fail if external approval
         * records now exist with entity_id = NULL.
         */
    }

    /**
     * Add an index only if an index with the supplied
     * name does not already exist.
     */
    private function addIndexIfMissing(
        string $table,
        array $columns,
        string $indexName
    ): void {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table(
            $table,
            function (Blueprint $blueprint) use (
                $columns,
                $indexName
            ) {
                $blueprint->index(
                    $columns,
                    $indexName
                );
            }
        );
    }

    /**
     * Drop index safely.
     */
    private function dropIndexIfExists(
        string $table,
        string $indexName
    ): void {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table(
            $table,
            function (Blueprint $blueprint) use (
                $indexName
            ) {
                $blueprint->dropIndex(
                    $indexName
                );
            }
        );
    }

    /**
     * Check index existence for MySQL/MariaDB.
     *
     * If the database driver does not support
     * SHOW INDEX, return false and allow Laravel
     * to handle index creation.
     */
    private function indexExists(
        string $table,
        string $indexName
    ): bool {
        try {
            $driver = DB::connection()
                ->getDriverName();

            if (
                in_array(
                    $driver,
                    ['mysql', 'mariadb'],
                    true
                )
            ) {
                $indexes = DB::select(
                    "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                    [$indexName]
                );

                return ! empty($indexes);
            }

            /*
             * SQLite
             */
            if ($driver === 'sqlite') {
                $indexes = DB::select(
                    "PRAGMA index_list('{$table}')"
                );

                foreach ($indexes as $index) {
                    if (
                        ($index->name ?? null)
                        === $indexName
                    ) {
                        return true;
                    }
                }
            }
        } catch (Throwable $e) {
            Log::warning(
                "Unable to check index {$indexName}: "
                .$e->getMessage()
            );
        }

        return false;
    }
};
