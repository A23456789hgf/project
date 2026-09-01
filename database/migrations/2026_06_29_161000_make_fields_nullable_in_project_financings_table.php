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
        Schema::table('project_financings', function (Blueprint $table) {
            $table->unsignedBigInteger('funding_source_id')->nullable()->change();
            $table->unsignedBigInteger('authority_id')->nullable()->change();
            $table->unsignedBigInteger('financing_type_id')->nullable()->change();
            $table->unsignedBigInteger('financing_form_id')->nullable()->change();
            $table->unsignedBigInteger('sub_financing_form_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_financings', function (Blueprint $table) {
            $table->unsignedBigInteger('funding_source_id')->nullable(false)->change();
            $table->unsignedBigInteger('authority_id')->nullable(false)->change();
            $table->unsignedBigInteger('financing_type_id')->nullable(false)->change();
            $table->unsignedBigInteger('financing_form_id')->nullable(false)->change();
        });
    }
};
