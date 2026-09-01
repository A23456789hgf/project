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
        if (! Schema::hasColumn('permissions', 'type')) {
            DB::statement("ALTER TABLE permissions ADD type NVARCHAR(255) DEFAULT 'action'");
        }
        if (! Schema::hasColumn('permissions', 'description')) {
            DB::statement('ALTER TABLE permissions ADD description NVARCHAR(MAX) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['type', 'description']);
        });
    }
};
