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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('user_name')->nullable()->after('user_id');
            $table->string('entity_name')->nullable()->after('user_name');
            $table->string('module')->nullable()->after('action');
            $table->string('url')->nullable()->after('module');
            $table->string('method')->nullable()->after('url');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['user_name', 'entity_name', 'module', 'url', 'method', 'user_agent']);
        });
    }
};
