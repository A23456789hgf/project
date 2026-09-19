<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * قواعد project_approvals بعد هذا Migration:
     *
     * Internal approval:
     * approver_scope = internal
     * entity_id       = internal_entities.id
     * authority_id    = NULL
     *
     * External approval:
     * approver_scope = external
     * entity_id       = NULL
     * authority_id    = authorities.id
     */
    public function up(): void
    {
        if (! Schema::hasTable('project_approvals')) {
            throw new RuntimeException(
                'جدول project_approvals غير موجود.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 1. entity_id
        |--------------------------------------------------------------------------
        |
        | يجب أن يكون nullable لأن المراحل الخارجية لا تستخدم entity_id.
        |
        */

        if (! Schema::hasColumn('project_approvals', 'entity_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('entity_id')
                    ->nullable()
                    ->after('project_id');
            });
        } else {
            /*
             * إزالة FK مؤقتاً إن وجد، ثم جعل العمود nullable.
             */
            $this->dropForeignIfExists(
                'project_approvals',
                'entity_id'
            );

            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('entity_id')
                    ->nullable()
                    ->change();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. approver_scope
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('project_approvals', 'approver_scope')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->string('approver_scope', 50)
                    ->nullable()
                    ->after('entity_id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. authority_id
        |--------------------------------------------------------------------------
        |
        | لا نحذفه.
        | هو العمود الصحيح للمراحل الخارجية.
        |
        */

        if (! Schema::hasColumn('project_approvals', 'authority_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('authority_id')
                    ->nullable()
                    ->after('approver_scope');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 4. إصلاح البيانات القديمة
        |--------------------------------------------------------------------------
        |
        | وجود authority_id يعني أن السجل خارجي.
        |
        */

        DB::table('project_approvals')
            ->whereNotNull('authority_id')
            ->update([
                'approver_scope' => 'external',
                'entity_id' => null,
            ]);

        /*
         * السجلات التي لديها entity_id ولا authority_id
         * هي سجلات داخلية.
         */
        DB::table('project_approvals')
            ->whereNotNull('entity_id')
            ->whereNull('authority_id')
            ->update([
                'approver_scope' => 'internal',
            ]);

        /*
         * سجلات قديمة لم يتم تحديد scope لها.
         *
         * لا نحاول تحويل ID من جدول إلى آخر.
         */
        DB::table('project_approvals')
            ->whereNull('approver_scope')
            ->whereNotNull('entity_id')
            ->update([
                'approver_scope' => 'internal',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 5. تنظيف scope
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | 6. فرض الفصل المنطقي بين الداخلي والخارجي
        |--------------------------------------------------------------------------
        */

        DB::table('project_approvals')
            ->where('approver_scope', 'external')
            ->whereNotNull('authority_id')
            ->update([
                'entity_id' => null,
            ]);

        DB::table('project_approvals')
            ->where('approver_scope', 'internal')
            ->whereNotNull('entity_id')
            ->update([
                'authority_id' => null,
            ]);

        /*
        |--------------------------------------------------------------------------
        | 7. Foreign Keys
        |--------------------------------------------------------------------------
        */

        $this->addForeignIfPossible(
            'project_approvals',
            'entity_id',
            'internal_entities',
            'id',
            'set null'
        );

        $this->addForeignIfPossible(
            'project_approvals',
            'authority_id',
            'authorities',
            'id',
            'set null'
        );

        /*
        |--------------------------------------------------------------------------
        | 8. Indexes
        |--------------------------------------------------------------------------
        */

        $this->addIndexIfPossible(
            'project_approvals',
            [
                'project_id',
                'entity_id',
            ],
            'project_approvals_project_entity_idx'
        );

        $this->addIndexIfPossible(
            'project_approvals',
            [
                'project_id',
                'authority_id',
            ],
            'project_approvals_project_authority_idx'
        );

        $this->addIndexIfPossible(
            'project_approvals',
            [
                'project_id',
                'approver_scope',
            ],
            'project_approvals_project_scope_idx'
        );

        /*
        |--------------------------------------------------------------------------
        | 9. فحص البيانات غير الصالحة
        |--------------------------------------------------------------------------
        */

        $invalidExternal = DB::table('project_approvals')
            ->where('approver_scope', 'external')
            ->whereNull('authority_id')
            ->count();

        if ($invalidExternal > 0) {
            Log::warning(
                "يوجد {$invalidExternal} سجل اعتماد خارجي بدون authority_id."
            );
        }

        $invalidInternal = DB::table('project_approvals')
            ->where('approver_scope', 'internal')
            ->whereNull('entity_id')
            ->count();

        if ($invalidInternal > 0) {
            Log::warning(
                "يوجد {$invalidInternal} سجل اعتماد داخلي بدون entity_id."
            );
        }

        $mixedRecords = DB::table('project_approvals')
            ->whereNotNull('entity_id')
            ->whereNotNull('authority_id')
            ->count();

        if ($mixedRecords > 0) {
            Log::warning(
                "يوجد {$mixedRecords} سجل يحتوي entity_id و authority_id معاً."
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * لا نعيد التصميم القديم الذي كان يخلط
     * Authority IDs مع InternalEntity IDs.
     */
    public function down(): void
    {
        if (! Schema::hasTable('project_approvals')) {
            return;
        }

        /*
         * نحذف فقط الفهارس التي أنشأها هذا Migration.
         *
         * لا نحذف:
         *
         * entity_id
         * authority_id
         * approver_scope
         *
         * لأن حذفها بعد وجود بيانات خارجية قد يؤدي
         * إلى فقدان بيانات أو خلط IDs.
         */

        $this->dropIndexIfPossible(
            'project_approvals',
            'project_approvals_project_entity_idx'
        );

        $this->dropIndexIfPossible(
            'project_approvals',
            'project_approvals_project_authority_idx'
        );

        $this->dropIndexIfPossible(
            'project_approvals',
            'project_approvals_project_scope_idx'
        );
    }

    /**
     * حذف Foreign Key إذا كان موجوداً.
     */
    private function dropForeignIfExists(
        string $table,
        string $column
    ): void {
        try {
            Schema::table(
                $table,
                function (Blueprint $blueprint) use ($column) {
                    $blueprint->dropForeign([$column]);
                }
            );
        } catch (Throwable $e) {
            /*
             * قد لا يكون FK موجوداً.
             * لا نوقف Migration بسبب ذلك.
             */
            Log::debug(
                "Foreign key for {$table}.{$column} was not dropped: "
                .$e->getMessage()
            );
        }
    }

    /**
     * إضافة Foreign Key بشكل آمن.
     */
    private function addForeignIfPossible(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete
    ): void {
        try {
            Schema::table(
                $table,
                function (Blueprint $blueprint) use (
                    $column,
                    $referencedTable,
                    $referencedColumn,
                    $onDelete
                ) {
                    $foreign = $blueprint
                        ->foreign($column)
                        ->references($referencedColumn)
                        ->on($referencedTable);

                    if ($onDelete === 'set null') {
                        $foreign->nullOnDelete();
                    } elseif ($onDelete === 'cascade') {
                        $foreign->cascadeOnDelete();
                    }
                }
            );
        } catch (Throwable $e) {
            Log::warning(
                "تعذر إضافة Foreign Key للعمود {$table}.{$column}: "
                .$e->getMessage()
            );
        }
    }

    /**
     * إضافة Index بدون إيقاف Migration
     * إذا كان موجوداً مسبقاً.
     */
    private function addIndexIfPossible(
        string $table,
        array $columns,
        string $indexName
    ): void {
        try {
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
        } catch (Throwable $e) {
            Log::debug(
                "Index {$indexName} may already exist: "
                .$e->getMessage()
            );
        }
    }

    /**
     * حذف Index بأمان.
     */
    private function dropIndexIfPossible(
        string $table,
        string $indexName
    ): void {
        try {
            Schema::table(
                $table,
                function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropIndex($indexName);
                }
            );
        } catch (Throwable $e) {
            Log::debug(
                "Index {$indexName} was not dropped: "
                .$e->getMessage()
            );
        }
    }
};
