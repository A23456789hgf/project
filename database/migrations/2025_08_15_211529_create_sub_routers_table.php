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
        Schema::create('sub_routers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_router_id')->constrained('main_routers')->onDelete('cascade'); // العلاقة مع الموجه الرئيسي
            $table->string('sub_router'); // اسم الموجه الفرعي
            $table->boolean('is_active')->default(true); // مفعّل أو لا
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_routers');
    }
};
