<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondence_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('correspondence_id');
            $table->text('reply_text');
            $table->json('attachments')->nullable();
            $table->date('return_date')->nullable();
            $table->unsignedBigInteger('replied_by_user_id');
            $table->timestamp('replied_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('correspondence_id')
                ->references('id')
                ->on('correspondences')
                ->onDelete('cascade');

            $table->foreign('replied_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('correspondence_id');
            $table->index('replied_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondence_replies');
    }
};
