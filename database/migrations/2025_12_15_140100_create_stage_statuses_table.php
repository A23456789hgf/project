<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique code for status (e.g., draft, pending, approved)');
            $table->string('name_ar')->comment('Arabic name of the status');
            $table->string('name_en')->nullable()->comment('English name of the status');
            $table->text('description_ar')->nullable()->comment('Arabic description of the status');
            $table->text('description_en')->nullable()->comment('English description of the status');
            $table->enum('category', ['draft', 'in_progress', 'completed', 'rejected', 'pending', 'revision'])->comment('Status category for grouping');
            $table->string('color')->default('#6c757d')->comment('Color badge hex value for UI');
            $table->string('icon')->nullable()->comment('Icon class for UI (e.g., fa-check-circle)');
            $table->integer('order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(true)->comment('Whether status is active');
            $table->boolean('is_system')->default(false)->comment('Whether this is a system status (cannot be deleted)');
            $table->timestamps();

            $table->index('code');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_statuses');
    }
};
