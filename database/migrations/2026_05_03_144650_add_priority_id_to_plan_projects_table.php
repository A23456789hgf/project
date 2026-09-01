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
        Schema::table('plan_projects', function (Blueprint $table) {
            // إضافة العمود priority_id بعد عمود plan_id
            $table->foreignId('priority_id')
                ->nullable()
                ->after('plan_id')
                ->constrained('priorities')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_projects', function (Blueprint $table) {
            // حذف المفتاح الأجنبي ثم العمود
            $table->dropForeign(['priority_id']);
            $table->dropColumn('priority_id');
        });
    }
};
