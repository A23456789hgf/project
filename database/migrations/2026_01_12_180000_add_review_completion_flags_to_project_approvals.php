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
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->boolean('financial_review_completed')->default(false)->after('technical_review_notes');
            $table->boolean('technical_review_completed')->default(false)->after('financial_review_completed');
            $table->dateTime('financial_review_completed_at')->nullable()->after('technical_review_completed');
            $table->dateTime('technical_review_completed_at')->nullable()->after('financial_review_completed_at');
            $table->unsignedBigInteger('financial_review_user_id')->nullable()->after('technical_review_completed_at');
            $table->unsignedBigInteger('technical_review_user_id')->nullable()->after('financial_review_user_id');

            // Optional: Add index for performance
            $table->index('financial_review_completed');
            $table->index('technical_review_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropColumn([
                'financial_review_completed',
                'technical_review_completed',
                'financial_review_completed_at',
                'technical_review_completed_at',
                'financial_review_user_id',
                'technical_review_user_id',
            ]);
        });
    }
};
