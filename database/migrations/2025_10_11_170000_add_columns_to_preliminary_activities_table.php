<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preliminary_activities', function (Blueprint $table) {
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->decimal('weight', 5, 2)->default(0);
        });

        Schema::table('preliminary_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('preliminary_activities', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'name', 'weight']);
        });
    }
};
