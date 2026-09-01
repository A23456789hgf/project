<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->text('required_action')->nullable()->after('notes')->comment('الإجراء المطلوب لحالة "بحاجة إلى إجراء"');
            $table->text('rejection_reason')->nullable()->after('required_action')->comment('سبب الرفض لحالة "مرفوض"');
            $table->json('attachments')->nullable()->after('attachment')->comment('مرفقات متعددة بصيغة JSON');
        });
    }

    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropColumn(['required_action', 'rejection_reason', 'attachments']);
        });
    }
};
