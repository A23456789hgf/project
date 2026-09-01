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
        Schema::table('empowerment_projects', function (Blueprint $table) {
            $table->unsignedBigInteger('geographic_scope_id')->nullable()->after('id');
            $table->unsignedBigInteger('administrative_scope_id')->nullable()->after('geographic_scope_id');

            $table->foreign('geographic_scope_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('administrative_scope_id')->references('id')->on('internal_entities')->onDelete('set null');
        });

        Schema::table('empowerment_beneficiaries', function (Blueprint $table) {
            $table->unsignedBigInteger('geographic_scope_id')->nullable()->after('id');
            $table->unsignedBigInteger('administrative_scope_id')->nullable()->after('geographic_scope_id');

            $table->foreign('geographic_scope_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('administrative_scope_id')->references('id')->on('internal_entities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empowerment_projects', function (Blueprint $table) {
            $table->dropForeign(['geographic_scope_id']);
            $table->dropForeign(['administrative_scope_id']);
            $table->dropColumn(['geographic_scope_id', 'administrative_scope_id']);
        });

        Schema::table('empowerment_beneficiaries', function (Blueprint $table) {
            $table->dropForeign(['geographic_scope_id']);
            $table->dropForeign(['administrative_scope_id']);
            $table->dropColumn(['geographic_scope_id', 'administrative_scope_id']);
        });
    }
};
