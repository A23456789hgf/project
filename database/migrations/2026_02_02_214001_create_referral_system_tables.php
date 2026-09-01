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
        Schema::create('referral_topics', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('entity_id')->constrained('internal_entities');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('referral_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('referral_topics')->onDelete('cascade');
            $table->string('referral_number')->unique();
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('from_department_id')->constrained('internal_entities');
            $table->foreignId('to_department_id')->constrained('internal_entities');
            $table->text('referral_text');
            $table->json('attachments');
            $table->dateTime('referral_date');

            $table->text('response_text')->nullable();
            $table->dateTime('response_date')->nullable();
            $table->json('response_attachments')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_activities');
        Schema::dropIfExists('referral_topics');
    }
};
