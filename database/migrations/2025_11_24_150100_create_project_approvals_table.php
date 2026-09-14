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
        if (! Schema::hasTable('project_approvals')) {
            Schema::create('project_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->unsignedBigInteger('authority_id')->nullable();
                $table->foreignId('approval_flow_id')->nullable()->references('id')->on('approval_flows')->onDelete('set null');
                $table->string('drop', 50)->nullable();
                $table->integer('step_order');
                $table->enum('status', ['pending', 'approved', 'rejected', 'needs_revision', 'on_hold'])->default('pending');
                $table->text('notes')->nullable();
                $table->string('attachment')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'authority_id']);
                $table->index(['project_id', 'step_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_approvals');
    }
};
