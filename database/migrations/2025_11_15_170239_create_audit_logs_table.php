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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User performing the action');
            $table->string('action')->comment('Type of action (create, update, delete, login, etc.)');
            $table->string('model_type')->nullable()->comment('Model/entity affected');
            $table->unsignedBigInteger('model_id')->nullable()->comment('ID of the entity affected');
            $table->json('old_values')->nullable()->comment('Previous values before change');
            $table->json('new_values')->nullable()->comment('New values after change');
            $table->text('description')->nullable()->comment('Description of the action');
            $table->string('ip_address')->nullable()->comment('IP address of the user');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('created_at');
            $table->index('model_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
