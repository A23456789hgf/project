<?php

// database/migrations/xxxx_xx_xx_create_villages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('governorate_id');
            $table->unsignedBigInteger('directorate_id');
            $table->unsignedBigInteger('sub_area_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['sub_area_id', 'name']);

            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('cascade');
            $table->foreign('directorate_id')->references('id')->on('directorates')->onDelete('cascade');
            $table->foreign('sub_area_id')->references('id')->on('sub_areas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('villages');
    }
};
