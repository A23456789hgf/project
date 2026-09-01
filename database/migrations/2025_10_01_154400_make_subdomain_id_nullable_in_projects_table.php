<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Disable foreign key constraints temporarily
            DB::statement('PRAGMA foreign_keys = OFF');

            // Create a temporary table with the new schema (matching exact column order)
            Schema::create('projects_temp', function (Blueprint $table) {
                $table->id(); // cid 0
                $table->string('project_name')->nullable(); // cid 1
                $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade'); // cid 2
                $table->foreignId('domain_id')->nullable()->constrained()->onDelete('cascade'); // cid 3
                $table->foreignId('subdomain_id')->nullable()->constrained('subdomains')->onDelete('cascade'); // cid 4 - NOW NULLABLE
                $table->foreignId('intervention_id')->nullable()->constrained('interventions')->onDelete('cascade'); // cid 5 - NOW NULLABLE
                $table->date('start_date_gregorian')->nullable(); // cid 6
                $table->string('start_date_hijri')->nullable(); // cid 7
                $table->date('end_date_gregorian')->nullable(); // cid 8
                $table->string('end_date_hijri')->nullable(); // cid 9
                $table->integer('number_of_beneficiaries')->nullable(); // cid 10
                $table->string('status')->default('draft'); // cid 11
                $table->timestamp('draft_saved_at')->nullable(); // cid 12
                $table->timestamp('finalized_at')->nullable(); // cid 13
                $table->timestamps(); // cid 14, 15
                $table->string('form_number')->unique()->nullable(); // cid 16
                $table->string('main_directives')->nullable(); // cid 17
                $table->string('subdirectives')->nullable(); // cid 18
                $table->string('priority')->nullable(); // cid 19
                $table->string('beneficiary_categories')->nullable(); // cid 20
                $table->text('target_categories')->nullable(); // cid 21
                $table->foreignId('priority_id')->nullable()->constrained('priorities')->onDelete('set null'); // cid 22
                $table->foreignId('main_router_id')->nullable()->constrained('main_routers')->onDelete('cascade'); // cid 23
                $table->foreignId('sub_router_id')->nullable()->constrained('sub_routers')->onDelete('cascade'); // cid 24
                $table->foreignId('target_category_id')->nullable()->constrained('target_categories')->onDelete('set null'); // cid 25
            });

            // Copy data from old table to new table (robust way for SQLite)
            $columns = Schema::getColumnListing('projects');
            $tempColumns = ['id', 'project_name', 'program_id', 'domain_id', 'subdomain_id', 'intervention_id',
                'start_date_gregorian', 'start_date_hijri', 'end_date_gregorian', 'end_date_hijri',
                'number_of_beneficiaries', 'status', 'draft_saved_at', 'finalized_at', 'created_at',
                'updated_at', 'form_number', 'main_directives', 'subdirectives', 'priority',
                'beneficiary_categories', 'target_categories', 'priority_id', 'main_router_id',
                'sub_router_id', 'target_category_id'];

            $sharedColumns = array_intersect($columns, $tempColumns);
            $columnList = implode(', ', $sharedColumns);

            DB::statement("INSERT INTO projects_temp ($columnList) SELECT $columnList FROM projects");

            // Drop the old table
            Schema::dropIfExists('projects');

            // Rename the temporary table to the original name
            Schema::rename('projects_temp', 'projects');

            // Re-enable foreign key constraints
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // For other databases (MySQL, PostgreSQL, etc.)
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('subdomain_id')->nullable()->change();
                $table->foreignId('intervention_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // For SQLite, we would need to recreate the table again
            // This is complex, so we'll skip it for now
            // In production, you should handle this properly
        } else {
            DB::transaction(function () {
                $subdomainAssignments = [];

                $projectsMissingSubdomain = DB::table('projects')
                    ->select('id', 'domain_id')
                    ->whereNull('subdomain_id')
                    ->lockForUpdate()
                    ->get();

                foreach ($projectsMissingSubdomain as $project) {
                    if (is_null($project->domain_id)) {
                        throw new RuntimeException('Cannot revert subdomain_id to NOT NULL: project '.$project->id.' has no domain reference.');
                    }

                    if (! array_key_exists($project->domain_id, $subdomainAssignments)) {
                        $subdomainAssignments[$project->domain_id] = DB::table('subdomains')
                            ->where('domain_id', $project->domain_id)
                            ->orderBy('id')
                            ->value('id');
                    }

                    $subdomainId = $subdomainAssignments[$project->domain_id];

                    if (is_null($subdomainId)) {
                        throw new RuntimeException('Cannot revert subdomain_id to NOT NULL: no subdomain found for domain '.$project->domain_id.'.');
                    }

                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update(['subdomain_id' => $subdomainId]);
                }

                $interventionBySubdomain = [];
                $interventionByDomain = [];

                $projectsMissingIntervention = DB::table('projects')
                    ->select('id', 'domain_id', 'subdomain_id')
                    ->whereNull('intervention_id')
                    ->lockForUpdate()
                    ->get();

                foreach ($projectsMissingIntervention as $project) {
                    $interventionId = null;

                    if (! is_null($project->subdomain_id)) {
                        if (! array_key_exists($project->subdomain_id, $interventionBySubdomain)) {
                            $interventionBySubdomain[$project->subdomain_id] = DB::table('interventions')
                                ->where('subdomain_id', $project->subdomain_id)
                                ->orderBy('id')
                                ->value('id');
                        }

                        $interventionId = $interventionBySubdomain[$project->subdomain_id];
                    } else {
                        if (is_null($project->domain_id)) {
                            throw new RuntimeException('Cannot revert intervention_id to NOT NULL: project '.$project->id.' has no domain reference.');
                        }

                        if (! array_key_exists($project->domain_id, $interventionByDomain)) {
                            $interventionByDomain[$project->domain_id] = DB::table('interventions')
                                ->where('domain_id', $project->domain_id)
                                ->orderBy('id')
                                ->value('id');
                        }

                        $interventionId = $interventionByDomain[$project->domain_id];
                    }

                    if (is_null($interventionId)) {
                        throw new RuntimeException('Cannot revert intervention_id to NOT NULL: no intervention found for project '.$project->id.'.');
                    }

                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update(['intervention_id' => $interventionId]);
                }

                Schema::table('projects', function (Blueprint $table) {
                    $table->foreignId('subdomain_id')->nullable(false)->change();
                    $table->foreignId('intervention_id')->nullable(false)->change();
                });
            });
        }
    }
};
