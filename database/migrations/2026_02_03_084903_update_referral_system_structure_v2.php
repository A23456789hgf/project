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
        Schema::table('referral_topics', function (Blueprint $table) {
            $table->string('topic_number')->unique()->after('id');
            $table->json('master_titles')->nullable()->after('subject');
        });

        Schema::table('referral_activities', function (Blueprint $table) {
            $table->dropUnique(['referral_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_activities', function (Blueprint $table) {
            $table->unique('referral_number');
        });

        Schema::table('referral_topics', function (Blueprint $table) {
            $table->dropColumn(['topic_number', 'master_titles']);
        });
    }
};
