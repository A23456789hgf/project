<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondence_movement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action_type'); // create, reply, referral, forward, return, close, etc.
            $table->string('action_description');
            $table->json('action_details')->nullable();
            $table->json('related_ids')->nullable();
            $table->string('from_entity')->nullable();
            $table->string('to_entity')->nullable();
            $table->timestamp('action_date')->useCurrent();
            $table->timestamps();

            $table->index('correspondence_id');
            $table->index('action_type');
            $table->index('action_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondence_movement_logs');
    }
};
