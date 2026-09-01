<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('special_objectives', function (Blueprint $table) {
            $table->dropColumn(['target_percentage', 'indicator_type']);
        });
    }

    public function down(): void
    {
        Schema::table('special_objectives', function (Blueprint $table) {
            $table->decimal('target_percentage', 5, 2)->nullable()->after('target_value');
            $table->enum('indicator_type', ['quantitative', 'relative', 'qualitative'])->default('quantitative')->after('target_percentage');
        });
    }
};
