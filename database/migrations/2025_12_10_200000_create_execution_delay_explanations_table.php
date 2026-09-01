<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('execution_delay_explanations')) {
            Schema::create('execution_delay_explanations', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('execution_type_id');
                $table->enum('execution_type', ['preliminary', 'executive']);

                $table->text('explanation');
                $table->json('attachments')->nullable();

                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('reviewer_notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

                $table->timestamps();

                $table->index('execution_type_id');
                $table->index('execution_type');
                $table->index('approval_status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('execution_delay_explanations');
    }
};
