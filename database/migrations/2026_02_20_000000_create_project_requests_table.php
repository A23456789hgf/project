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
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->string('project_name');
            $table->json('project_data')->nullable();
            $table->foreignId('program_id')->nullable()->constrained();
            $table->foreignId('domain_id')->nullable()->constrained();
            $table->foreignId('subdomain_id')->nullable()->constrained('subdomains');
            $table->foreignId('intervention_id')->nullable()->constrained('interventions');
            $table->date('start_date_gregorian')->nullable();
            $table->string('start_date_hijri')->nullable();
            $table->date('end_date_gregorian')->nullable();
            $table->string('end_date_hijri')->nullable();
            $table->integer('number_of_beneficiaries')->nullable();
            $table->string('status')->default('draft');
            $table->integer('last_saved_step')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->string('assigned_project_number')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_requests');
    }
};
