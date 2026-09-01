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
        Schema::create('entity_officers', function (Blueprint $row) {
            $row->id();
            $row->string('entity_type'); // internal or external
            $row->unsignedBigInteger('internal_entity_id')->nullable();
            $row->unsignedBigInteger('authority_id')->nullable();
            $row->string('admin_name');
            $row->string('job_title');

            $row->foreign('internal_entity_id')->references('id')->on('internal_entities')->onDelete('cascade');
            $row->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');

            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_officers');
    }
};
