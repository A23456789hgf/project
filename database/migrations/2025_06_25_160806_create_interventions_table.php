<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id(); // المفتاح الأساسي
            $table->foreignId('domain_id')->constrained('domains');
            $table->foreignId('subdomain_id')->constrained('subdomains');
            $table->string('name'); // اسم التدخل ويجب أن يكون فريدًا
            $table->boolean('is_active')->default(true); // حالة التفعيل
            $table->timestamps(); // التواريخ
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
