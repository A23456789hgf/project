<?php

namespace App\Console\Commands;

use App\Http\Controllers\Project\Services\ProjectService;
use App\Models\EmpowermentProject;
use App\Models\Project;
use Illuminate\Console\Command;

class SyncEmpowermentProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:sync-empowerment {--project= : Specific project ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync projects containing loans (قروض) or Tamkeen (تمكين) to empowerment_projects table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ProjectService $projectService)
    {
        $projectId = $this->option('project');

        $query = Project::query();

        if ($projectId) {
            $query->where('id', $projectId);
        }

        $totalCount = (clone $query)->count();
        $this->info("Found {$totalCount} total projects to inspect. Starting sync...");

        $bar = $this->output->createProgressBar($totalCount);
        $syncedCount = 0;

        $query->chunkById(200, function ($projects) use ($projectService, &$syncedCount, $bar) {
            foreach ($projects as $project) {
                try {
                    $beforeCount = EmpowermentProject::where('project_id', $project->id)->count();

                    $projectService->syncProjectToEmpowermentDepartment($project);

                    $afterCount = EmpowermentProject::where('project_id', $project->id)->count();

                    if ($afterCount > 0 && $beforeCount === 0) {
                        $syncedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error(" Failed for project ID {$project->id}: ".$e->getMessage());
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Sync completed! Processed {$totalCount} projects. Total synced to Empowerment/Loans page: {$syncedCount}. Total records in Empowerment table now: ".EmpowermentProject::count().'.');

        return 0;
    }
}
