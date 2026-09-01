<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_correspondence_movements_table.php
    public function up()
    {
        Schema::create('correspondence_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('from_entity_id')->nullable()->constrained('internal_entities');
            $table->foreignId('to_entity_id')->nullable()->constrained('internal_entities');
            $table->string('type'); // create, reply, referral, forward, return, close, status_change
            $table->string('description');
            $table->text('metadata')->nullable(); // لتخزين بيانات إضافية بصيغة JSON
            $table->timestamps();

            $table->index('correspondence_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondence_movements');
    }
};
