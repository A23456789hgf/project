<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('preliminary_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_id')->constrained('preliminary_activities')->onDelete('cascade');
            $table->string('procedure_name');
            $table->decimal('weight', 5, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->default(0);
            $table->text('verification_means')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'activity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('preliminary_procedures');
    }
};
