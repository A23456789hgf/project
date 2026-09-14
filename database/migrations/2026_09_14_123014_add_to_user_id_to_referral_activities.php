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
        Schema::table('referral_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('to_user_id')->nullable()->after('to_department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_activities', function (Blueprint $table) {
            $table->dropColumn('to_user_id');
        });
    }
};
