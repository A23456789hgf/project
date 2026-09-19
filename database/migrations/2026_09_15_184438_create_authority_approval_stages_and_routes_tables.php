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
        if (! Schema::hasTable('authority_approval_stages')) {
            Schema::create('authority_approval_stages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('authority_id');
                $table->string('stage', 50);
                $table->integer('stage_order');
                $table->unsignedBigInteger('responsible_user_id')->nullable();
                $table->boolean('is_active')->default(true);

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');
                $table->foreign('responsible_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                // A single authority can only have each stage type defined once
                $table->unique(['authority_id', 'stage']);
            });
        }

        if (! Schema::hasTable('authority_approval_routes')) {
            Schema::create('authority_approval_routes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('authority_id')->unique();
                $table->string('destination_type', 50); // 'ministry' or 'authority'
                $table->unsignedBigInteger('destination_authority_id')->nullable();
                $table->boolean('is_active')->default(true);

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');
                $table->foreign('destination_authority_id')->references('id')->on('authorities')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authority_approval_routes');
        Schema::dropIfExists('authority_approval_stages');
    }
};
