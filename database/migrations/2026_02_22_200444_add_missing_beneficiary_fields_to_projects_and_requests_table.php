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
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'number_of_beneficiary_families')) {
                $table->integer('number_of_beneficiary_families')->nullable()->after('number_of_beneficiaries');
            }
        });

        Schema::table('project_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('project_requests', 'beneficiary_categories')) {
                $table->string('beneficiary_categories')->nullable()->after('end_date_hijri');
            }
            if (! Schema::hasColumn('project_requests', 'number_of_beneficiary_families')) {
                $table->integer('number_of_beneficiary_families')->nullable()->after('number_of_beneficiaries');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('number_of_beneficiary_families');
        });

        Schema::table('project_requests', function (Blueprint $table) {
            $table->dropColumn(['beneficiary_categories', 'number_of_beneficiary_families']);
        });
    }
};
