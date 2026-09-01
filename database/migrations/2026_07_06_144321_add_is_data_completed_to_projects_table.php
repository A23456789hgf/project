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
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_data_completed')->default(false)->after('project_type')->comment('للمشاريع القديمة: هل تم استكمال البيانات مرة واحدة');
            $table->timestamp('data_completed_at')->nullable()->after('is_data_completed')->comment('تاريخ استكمال البيانات');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_data_completed', 'data_completed_at']);
        });
    }
};
