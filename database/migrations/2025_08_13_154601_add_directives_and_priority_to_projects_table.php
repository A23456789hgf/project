<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('main_directives')->nullable()->after('number_of_beneficiaries');
            $table->string('subdirectives')->nullable()->after('main_directives');
            $table->string('priority')->nullable()->after('subdirectives');
            $table->string('beneficiary_categories')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['main_directives', 'subdirectives', 'priority', 'beneficiary_categories']);
        });
    }
};
