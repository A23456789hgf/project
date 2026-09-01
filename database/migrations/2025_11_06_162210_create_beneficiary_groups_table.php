<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficiaryGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('beneficiary_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(1); // العمود المفقود
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('beneficiary_groups');
    }
}
