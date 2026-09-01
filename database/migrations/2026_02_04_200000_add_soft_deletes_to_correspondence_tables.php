<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('correspondence_referrals', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('correspondence_referrals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
