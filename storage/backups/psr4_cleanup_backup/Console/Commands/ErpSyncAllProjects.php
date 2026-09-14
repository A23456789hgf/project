<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ErpNextService;
use Illuminate\Console\Command;

class ErpSyncAllProjects extends Command
{
    protected $signature = 'erp:sync-all-projects';

    protected $description = 'Send all projects in implementation stage to ERPNext';

    public function handle(ErpNextService $erpService)
    {
        $this->info('Starting ERPNext sync for all projects in implementation stage...');

        $projects = Project::where('status', 'implementation')->get();

        if ($projects->isEmpty()) {
            $this->info('No projects found in implementation stage.');

            return 0;
        }

        foreach ($projects as $project) {
            try {
                $erpService->sendProject($project);
                $this->info("Project {$project->id} synced successfully.");
            } catch (\Exception $e) {
                $this->error("Project {$project->id} failed.");
            }
        }

        return 0;
    }
}
