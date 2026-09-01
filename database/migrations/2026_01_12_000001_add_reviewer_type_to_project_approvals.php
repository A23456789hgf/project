<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            // Add reviewer type to track if review was done by financial or technical reviewer
            if (! Schema::hasColumn('project_approvals', 'reviewer_type')) {
                $table->enum('reviewer_type', ['financial', 'technical', 'general'])->nullable()->after('status')->comment('نوع المراجع: مالي، فني، أو عام');
            }

            // Add financial review notes if not exists
            if (! Schema::hasColumn('project_approvals', 'financial_review_notes')) {
                $table->text('financial_review_notes')->nullable()->after('rejection_reason')->comment('ملاحظات المراجعة المالية');
            }

            // Add technical review notes
            if (! Schema::hasColumn('project_approvals', 'technical_review_notes')) {
                $table->text('technical_review_notes')->nullable()->after('financial_review_notes')->comment('ملاحظات المراجعة الفنية');
            }
        });

        Schema::table('project_approvals', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('project_approvals'))->pluck('name')->toArray();

            if (! in_array('project_approvals_reviewer_type_index', $indexes)) {
                $table->index('reviewer_type');
            }
            if (! in_array('project_approvals_status_reviewer_type_index', $indexes)) {
                $table->index(['status', 'reviewer_type']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropIndex(['project_approvals_reviewer_type_index']);
            $table->dropIndex(['project_approvals_status_reviewer_type_index']);
            $table->dropColumn(['reviewer_type', 'technical_review_notes']);

            // Only drop financial_review_notes if it was added by this migration
            if (Schema::hasColumn('project_approvals', 'financial_review_notes')) {
                $table->dropColumn('financial_review_notes');
            }
        });
    }
};
