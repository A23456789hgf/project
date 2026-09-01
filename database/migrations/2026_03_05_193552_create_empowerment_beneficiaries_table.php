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
        Schema::create('empowerment_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empowerment_project_id')->constrained('empowerment_projects')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('id_number');
            $table->foreignId('governorate_id')->constrained('governorates');
            $table->foreignId('directorate_id')->constrained('directorates');
            $table->foreignId('sub_area_id')->nullable()->constrained('sub_areas');
            $table->foreignId('village_id')->nullable()->constrained('villages');
            $table->decimal('loan_amount', 15, 2);
            $table->string('repayment_method'); // monthly, annually, seasonally
            $table->integer('installments_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empowerment_beneficiaries');
    }
};
