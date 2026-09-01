<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_descends', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_linked_to_project')->default(false);
            $table->unsignedBigInteger('project_id')->nullable();
            $table->text('needs')->nullable();
            $table->text('reason_for_drop')->nullable();
            $table->text('objective_of_drop')->nullable();
            $table->enum('priority', ['Important', 'Urgent'])->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_descends');
    }
};
