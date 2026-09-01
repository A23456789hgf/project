<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_financings', function (Blueprint $table) {
            $table->id();

            // FK columns
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funding_source_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('authority_id');
            $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');

            $table->foreignId('financing_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financing_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_financing_form_id')->nullable()->constrained()->nullOnDelete();

            // بيانات التمويل
            $table->decimal('financing_amount', 15, 2)->default(0);
            $table->decimal('financing_percentage', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_financings');
    }
};
