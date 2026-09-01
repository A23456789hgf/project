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
        Schema::table('permissions', function (Blueprint $table) {
            // Track if permission was auto-registered from routes
            $table->boolean('auto_registered')->default(false)->after('module');

            // Store controller information for debugging and documentation
            $table->string('controller_class')->nullable()->after('auto_registered');
            $table->string('controller_method')->nullable()->after('controller_class');

            // Add index for efficient querying
            $table->index(['auto_registered', 'module'], 'idx_auto_registered_module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex('idx_auto_registered_module');
            $table->dropColumn(['auto_registered', 'controller_class', 'controller_method']);
        });
    }
};
