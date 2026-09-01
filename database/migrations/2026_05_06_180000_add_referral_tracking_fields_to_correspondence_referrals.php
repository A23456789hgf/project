<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix invalid default value for referred_at column using raw SQL to avoid Doctrine DBAL issues with timestamp type
        if (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE correspondence_referrals MODIFY referred_at TIMESTAMP NULL');
        }

        Schema::table('correspondence_referrals', function (Blueprint $table) {
            // Add referred_to_type (person/department)
            if (! Schema::hasColumn('correspondence_referrals', 'referred_to_type')) {
                $table->string('referred_to_type')->nullable()->after('referred_to_entity_id');
            }

            // Add referred_to_name
            if (! Schema::hasColumn('correspondence_referrals', 'referred_to_name')) {
                $table->string('referred_to_name')->nullable()->after('referred_to_type');
            }

            // Add referral_status (pending, accepted, completed, rejected)
            if (! Schema::hasColumn('correspondence_referrals', 'referral_status')) {
                $table->string('referral_status')->default('pending')->after('status');
            }

            // Add referral_date
            if (! Schema::hasColumn('correspondence_referrals', 'referral_date')) {
                $table->timestamp('referral_date')->nullable()->after('referral_status');
            }

            // Add referral_notes
            if (! Schema::hasColumn('correspondence_referrals', 'referral_notes')) {
                $table->text('referral_notes')->nullable()->after('referral_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correspondence_referrals', function (Blueprint $table) {
            $table->dropColumn([
                'referred_to_type',
                'referred_to_name',
                'referral_status',
                'referral_date',
                'referral_notes',
            ]);
        });
    }
};
