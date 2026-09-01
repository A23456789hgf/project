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
        Schema::create('erpnext_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->text('url')->nullable();
            $table->integer('response_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->string('erpnext_docname')->nullable();
            $table->string('status')->default('failed'); // success, failed, skipped
            $table->timestamps();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->index('project_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erpnext_sync_logs');
    }
};
