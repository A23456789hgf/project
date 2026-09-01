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
        Schema::create('request_descend_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_descend_id');
            $table->string('name');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('work')->nullable();
            $table->decimal('daily_amount', 10, 2)->default(0);
            $table->decimal('duration', 8, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('request_descend_id')->references('id')->on('request_descends')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_descend_members');
    }
};
