<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiary_group_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['beneficiary_group_id', 'project_id'], 'beneficiary_group_project_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiary_group_project');
    }
};
