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
        Schema::table('authorities', function (Blueprint $table) {
            if (! Schema::hasColumn('authorities', 'entity_scope')) {
                $table->string('entity_scope')->nullable()->after('directorate_id')->comment('internal or external scope');
            }
            if (! Schema::hasColumn('authorities', 'financing_type_id')) {
                $table->foreignId('financing_type_id')->nullable()->after('entity_scope')->constrained('financing_types')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authorities', function (Blueprint $table) {
            if (Schema::hasColumn('authorities', 'financing_type_id')) {
                $table->dropForeign(['financing_type_id']);
                $table->dropColumn('financing_type_id');
            }
            if (Schema::hasColumn('authorities', 'entity_scope')) {
                $table->dropColumn('entity_scope');
            }
        });
    }
};
