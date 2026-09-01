<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds referral_attachments and response_attachments (JSON/text) columns
     * to support multiple attachments. The old singular columns are kept for
     * backward compatibility and can be migrated / dropped later.
     */
    public function up(): void
    {
        Schema::table('project_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('project_referrals', 'referral_attachments')) {
                $table->text('referral_attachments')->nullable()->after('referral_attachment');
            }
            if (! Schema::hasColumn('project_referrals', 'response_attachments')) {
                $table->text('response_attachments')->nullable()->after('response_attachment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_referrals', function (Blueprint $table) {
            if (Schema::hasColumn('project_referrals', 'referral_attachments')) {
                $table->dropColumn('referral_attachments');
            }
            if (Schema::hasColumn('project_referrals', 'response_attachments')) {
                $table->dropColumn('response_attachments');
            }
        });
    }
};
