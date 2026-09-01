<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل المايغريشن.
     */
    public function up(): void
    {
        Schema::create('main_routers', function (Blueprint $table) {
            $table->id(); // عمود ID تلقائي
            $table->string('main_router')->unique();
            $table->boolean('is_active')->default(true);

            $table->timestamps(); // created_at و updated_at
        });
    }

    /**
     * التراجع عن المايغريشن.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_routers');
    }
};
