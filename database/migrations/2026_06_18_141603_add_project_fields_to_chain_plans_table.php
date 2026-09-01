<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chain_plans', function (Blueprint $table) {

            // اسم المشروع
            $table->string('project_name')->nullable()->after('domain_id');

            // اسم النشاط
            $table->string('activity_name')->nullable()->after('project_name');

            // الجهة المنفذة
            $table->foreignId('authority_id')
                ->nullable()
                ->after('value_chain_financing_type_id')
                ->constrained('authorities')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chain_plans', function (Blueprint $table) {

            $table->dropForeign(['authority_id']);

            $table->dropColumn([
                'project_name',
                'activity_name',
                'authority_id',
            ]);
        });
    }
};
