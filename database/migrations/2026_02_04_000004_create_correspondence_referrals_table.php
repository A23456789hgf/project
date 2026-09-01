<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondence_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('correspondence_id');
            $table->unsignedBigInteger('referred_to_entity_id');
            $table->text('referral_text');
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('referred_by_user_id');
            $table->timestamp('referred_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('correspondence_id')
                ->references('id')
                ->on('correspondences')
                ->onDelete('cascade');

            $table->foreign('referred_to_entity_id')
                ->references('id')
                ->on('internal_entities')
                ->onDelete('cascade');

            $table->foreign('referred_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('correspondence_id');
            $table->index('referred_to_entity_id');
            $table->index('referred_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondence_referrals');
    }
};
