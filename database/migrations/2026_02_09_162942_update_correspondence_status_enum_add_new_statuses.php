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
        Schema::table('correspondences', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        // Add status to referrals if missing
        if (! Schema::hasColumn('correspondence_referrals', 'status')) {
            Schema::table('correspondence_referrals', function (Blueprint $table) {
                $table->string('status')->default('pending');
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('completed_by_user_id')->nullable();

                $table->foreign('completed_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            $table->enum('status', ['pending', 'replied', 'referred', 'closed'])->default('pending')->change();
        });

        Schema::table('correspondence_referrals', function (Blueprint $table) {
            $table->dropForeign(['completed_by_user_id']);
            $table->dropColumn(['status', 'completed_at', 'completed_by_user_id']);
        });
    }
};
