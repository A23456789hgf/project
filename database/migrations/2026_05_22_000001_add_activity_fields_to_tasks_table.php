<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_within_activities')->default(false)->after('due_date');
            $table->string('activity_type')->nullable()->after('is_within_activities'); // 'preliminary' or 'executive'
            $table->unsignedBigInteger('activity_id')->nullable()->after('activity_type');
            $table->unsignedBigInteger('procedure_id')->nullable()->after('activity_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_within_activities', 'activity_type', 'activity_id', 'procedure_id']);
        });
    }
};
