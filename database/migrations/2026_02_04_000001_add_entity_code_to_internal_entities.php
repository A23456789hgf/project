<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->string('entity_code', 2)->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('internal_entities', function (Blueprint $table) {
            $table->dropColumn('entity_code');
        });
    }
};
