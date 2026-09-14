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
        Schema::table('project_referrals', function (Blueprint $table) {
            $table->unsignedBigInteger('referred_user_id')->nullable()->after('referred_entity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_referrals', function (Blueprint $table) {
            $table->dropColumn('referred_user_id');
        });
    }
};
