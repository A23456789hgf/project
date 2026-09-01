<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('internal_entities')) {
            Schema::create('internal_entities', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('parent_id')
                    ->references('id')
                    ->on('internal_entities')
                    ->onDelete('cascade');

                $table->index('parent_id');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_entities');
    }
};
