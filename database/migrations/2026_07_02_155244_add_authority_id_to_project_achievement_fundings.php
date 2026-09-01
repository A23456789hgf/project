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
        Schema::table('project_achievement_fundings', function (Blueprint $table) {
            // التحقق من وجود العمود قبل إضافته
            if (! Schema::hasColumn('project_achievement_fundings', 'authority_id')) {
                $table->foreignId('authority_id')->nullable()->after('id')
                    ->constrained('authorities')->onDelete('restrict');
            }
            // تغيير nullable لـ funding_entity إذا كان موجوداً وغير nullable
            if (Schema::hasColumn('project_achievement_fundings', 'funding_entity')) {
                $table->string('funding_entity')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_achievement_fundings', function (Blueprint $table) {
            $table->dropForeign(['authority_id']);
            $table->dropColumn('authority_id');
            $table->string('funding_entity')->nullable(false)->change();
        });
    }
};
