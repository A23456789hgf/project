<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_descend_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_descend_id');
            $table->string('activity')->nullable();
            $table->string('expected_output')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('request_descend_id')->references('id')->on('request_descends')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_descend_activities');
    }
};
