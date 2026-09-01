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
        Schema::create('memoirs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('memoir_number')->unique();
            $blueprint->string('to');
            $blueprint->string('subject');
            $blueprint->text('body');
            $blueprint->date('gregorian_date');
            $blueprint->string('hijri_date');
            // Relationship to Project
            $blueprint->unsignedBigInteger('project_id')->nullable();

            // Tracking & Scoping (HasCreatorTracking Trait Columns)
            $blueprint->string('creator_username')->nullable();
            $blueprint->unsignedBigInteger('creator_entity_id')->nullable();

            // Additional Scoping (HasDomainScope Trait Columns)
            $blueprint->unsignedBigInteger('geographic_scope_id')->nullable();
            $blueprint->unsignedBigInteger('administrative_scope_id')->nullable();

            // Legacy Tracking (Optional but kept for compatibility)
            $blueprint->unsignedBigInteger('created_by')->nullable();
            $blueprint->unsignedBigInteger('entity_id')->nullable();

            $blueprint->foreign('project_id')->references('id')->on('projects')->onDelete('set null');

            $blueprint->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $blueprint->foreign('entity_id')->references('id')->on('internal_entities')->onDelete('set null');

            $blueprint->softDeletes();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memoirs');
    }
};
