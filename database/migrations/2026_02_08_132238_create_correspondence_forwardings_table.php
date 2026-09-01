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
        Schema::create('correspondence_forwardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->onDelete('cascade');
            $table->foreignId('from_entity_id')->constrained('internal_entities')->onDelete('cascade');
            $table->foreignId('to_entity_id')->constrained('internal_entities')->onDelete('cascade');
            $table->foreignId('forwarded_by_user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, acknowledged
            $table->timestamp('forwarded_at')->useCurrent();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondence_forwardings');
    }
};
