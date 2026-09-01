<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->string('correspondence_number')->unique();
            $table->string('subject');
            $table->text('message_body');
            $table->unsignedBigInteger('sender_entity_id');
            $table->unsignedBigInteger('sender_user_id');
            $table->unsignedBigInteger('recipient_entity_id');
            $table->string('priority')->default('normal');
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['pending', 'replied', 'referred', 'closed'])->default('pending');
            $table->timestamps();

            // Foreign keys
            $table->foreign('sender_entity_id')
                ->references('id')
                ->on('internal_entities')
                ->onDelete('cascade');

            $table->foreign('recipient_entity_id')
                ->references('id')
                ->on('internal_entities')
                ->onDelete('cascade');

            $table->foreign('sender_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('correspondence_number');
            $table->index('sender_entity_id');
            $table->index('recipient_entity_id');
            $table->index('sender_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondences');
    }
};
