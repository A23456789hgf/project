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
        // Fix correspondence_replies
        Schema::table('correspondence_replies', function (Blueprint $table) {
            if (! Schema::hasColumn('correspondence_replies', 'status')) {
                $table->string('status')->default('sent');
            }
            if (! Schema::hasColumn('correspondence_replies', 'confidential')) {
                $table->boolean('confidential')->default(false);
            }
            if (! Schema::hasColumn('correspondence_replies', 'acknowledged_at')) {
                $table->timestamp('acknowledged_at')->nullable();
            }
            if (! Schema::hasColumn('correspondence_replies', 'acknowledged_by_user_id')) {
                $table->unsignedBigInteger('acknowledged_by_user_id')->nullable();
                $table->foreign('acknowledged_by_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Fix correspondence_referrals
        Schema::table('correspondence_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('correspondence_referrals', 'priority')) {
                $table->string('priority')->default('normal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->dropForeign(['acknowledged_by_user_id']);
            $table->dropColumn(['status', 'confidential', 'acknowledged_at', 'acknowledged_by_user_id']);
        });

        Schema::table('correspondence_referrals', function (Blueprint $table) {
            $table->dropColumn(['priority']);
        });
    }
};
