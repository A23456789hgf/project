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
        Schema::create('correspondence_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('correspondence_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // created, replied, returned, referred, closed, restored, updated
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('correspondence_id')->references('id')->on('correspondences')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondence_activities');
    }
};
