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
        Schema::create('project_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('drop')->nullable(); // Stage identifier (e.g., 'entity_1')
            $table->foreignId('stage_id')->nullable()->constrained('stages')->onDelete('set null');

            // Referring entity (sender)
            $table->foreignId('referring_entity_id')->constrained('internal_entities')->onDelete('cascade');
            $table->foreignId('referring_user_id')->constrained('users')->onDelete('cascade');

            // Referred entity (receiver)
            $table->foreignId('referred_entity_id')->constrained('internal_entities')->onDelete('cascade');

            // Referral content
            $table->text('referral_text');
            $table->string('referral_attachment')->nullable();

            // Response content
            $table->text('response_text')->nullable();
            $table->string('response_attachment')->nullable();
            $table->foreignId('responding_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'responded', 'returned'])->default('pending');

            $table->timestamps();

            // Indexes for performance
            $table->index(['project_id', 'status']);
            $table->index(['referred_entity_id', 'status']);
            $table->index('referring_entity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_referrals');
    }
};
