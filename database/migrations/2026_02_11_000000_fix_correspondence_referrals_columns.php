<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correspondence_referrals', function (Blueprint $table) {
            // Rename attachment_path to attachments if it exists
            if (Schema::hasColumn('correspondence_referrals', 'attachment_path')) {
                $table->renameColumn('attachment_path', 'attachments');
            }

            // Add attachments if it doesn't exist and attachment_path didn't exist
            if (! Schema::hasColumn('correspondence_referrals', 'attachments')) {
                $table->json('attachments')->nullable()->after('referral_text');
            }

            // Add deadline if it doesn't exist
            if (! Schema::hasColumn('correspondence_referrals', 'deadline')) {
                $table->timestamp('deadline')->nullable()->after('attachments');
            }

            // Add notes if it doesn't exist
            if (! Schema::hasColumn('correspondence_referrals', 'notes')) {
                $table->text('notes')->nullable()->after('priority');
            }
        });

        // Ensure attachments is JSON type (for MySQL)
        if (config('database.default') === 'mysql') {
            try {
                DB::statement('ALTER TABLE correspondence_referrals MODIFY attachments JSON NULL');
            } catch (Exception $e) {
                // If JSON is not supported or already correct, ignore
            }
        }
    }

    public function down(): void
    {
        Schema::table('correspondence_referrals', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'notes']);
            if (Schema::hasColumn('correspondence_referrals', 'attachments')) {
                $table->renameColumn('attachments', 'attachment_path');
            }
        });
    }
};
