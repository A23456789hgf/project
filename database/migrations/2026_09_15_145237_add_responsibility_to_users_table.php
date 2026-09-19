<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the `responsibility` column to the users table.
     * This column stores the user's approval workflow classification:
     *   TECHNICAL    → المراجع الفني
     *   FINANCIAL    → المراجع المالي
     *   ENTITY_APPROVER → مسؤول الجهة (اعتماد)
     *   NULL         → لا يشارك في دورة الموافقات
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('responsibility', 30)->nullable()->after('status')
                ->comment('User approval responsibility: TECHNICAL, FINANCIAL, ENTITY_APPROVER, or null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('responsibility');
        });
    }
};
