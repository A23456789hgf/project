<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correspondence_replies', function (Blueprint $table) {
            if (! Schema::hasColumn('correspondence_replies', 'referral_id')) {
                $table->unsignedBigInteger('referral_id')->nullable()->after('correspondence_id');
                $table->foreign('referral_id')
                    ->references('id')
                    ->on('correspondence_forwardings')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropColumn('referral_id');
        });
    }
};
