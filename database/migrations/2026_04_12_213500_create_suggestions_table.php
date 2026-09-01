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
        if (! Schema::hasTable('suggestions')) {
            Schema::create('suggestions', function (Blueprint $table) {
                $table->id();
                $table->text('content');
                $table->date('gregorian_date');
                $table->string('hijri_date');
                $table->boolean('is_completed')->default(false);

                // Tracking & Ownership
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('entity_id')->nullable()->constrained('internal_entities')->onDelete('cascade');

                // Geographic Data (for scoping)
                $table->foreignId('governorate_id')->nullable()->constrained('governorates')->onDelete('set null');
                $table->foreignId('directorate_id')->nullable()->constrained('directorates')->onDelete('set null');

                // Scoping columns (for HasDomainScope trait)
                $table->foreignId('geographic_scope_id')->nullable()->constrained('governorates')->onDelete('set null');
                $table->foreignId('administrative_scope_id')->nullable()->constrained('internal_entities')->onDelete('set null');

                // Audit Tracking
                $table->string('creator_username')->nullable();
                $table->foreignId('creator_entity_id')->nullable()->constrained('internal_entities')->onDelete('set null');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
