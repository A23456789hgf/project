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
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->string('erpnext_id')->nullable()->after('entity_type');
            $table->string('erpnext_type')->nullable()->after('erpnext_id');
            $table->string('erpnext_parent_id')->nullable()->after('erpnext_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->dropColumn(['erpnext_id', 'erpnext_type', 'erpnext_parent_id']);
        });
    }
};
