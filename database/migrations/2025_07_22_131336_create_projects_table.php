<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('form_number')->unique();
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('domain_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subdomain_id')->nullable()->constrained('subdomains')->onDelete('cascade');
            $table->foreignId('intervention_id')->nullable()->constrained('interventions')->onDelete('cascade');

            $table->date('start_date_gregorian')->nullable();
            $table->string('start_date_hijri')->nullable();
            $table->date('end_date_gregorian')->nullable();
            $table->string('end_date_hijri')->nullable();
            $table->integer('number_of_beneficiaries')->nullable();

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->integer('last_saved_step')->default(1);
            $table->timestamp('draft_saved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('status');
            $table->index('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
