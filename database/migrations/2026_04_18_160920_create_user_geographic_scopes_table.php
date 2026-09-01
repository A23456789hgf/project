<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('user_geographic_scopes');
        Schema::create('user_geographic_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('governorate_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('directorate_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            // التأكد من وجود محافظة أو مديرية على الأقل
            $table->index(['user_id', 'governorate_id', 'directorate_id'], 'user_geo_scopes_composite_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_geographic_scopes');
    }
};
