<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique code for stage (e.g., assembly, union, committee)');
            $table->string('name_ar')->comment('Arabic name of the stage');
            $table->string('name_en')->nullable()->comment('English name of the stage');
            $table->text('description_ar')->nullable()->comment('Arabic description of the stage');
            $table->text('description_en')->nullable()->comment('English description of the stage');
            $table->integer('order')->default(0)->comment('Order of stage in workflow sequence');
            $table->enum('type', ['assessment', 'approval', 'implementation', 'execution'])->default('approval')->comment('Type of stage');
            $table->boolean('is_active')->default(true)->comment('Whether stage is active');
            $table->boolean('is_system')->default(false)->comment('Whether this is a system stage (cannot be deleted)');
            $table->timestamps();

            $table->index('order');
            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
