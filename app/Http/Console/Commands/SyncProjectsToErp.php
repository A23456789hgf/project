<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\ErpProjectController;
use App\Models\Project;
use Illuminate\Console\Command;

class SyncProjectsToErp extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature = 'erp:sync-projects {project? : Optional project ID to sync a specific project}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync projects in implementation stage to external ERP system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->argument('project');
        $controller = app(ErpProjectController::class);

        if ($projectId) {
            $this->info("Syncing specific project ID: {$projectId}...");
            $project = Project::find($projectId);

            if (! $project) {
                $this->error('Project not found.');

                return 1;
            }

            $success = $controller->syncProjectToErp($project);
            if ($success) {
                $this->info('✅ Project successfully synced.');
            } else {
                $this->error('❌ Project sync failed.');
            }

            return 0;
        }

        $this->info('Starting batch ERP project synchronization for all projects in implementation stage...');

        $response = $controller->syncAllImplementationProjects();
        $data = json_decode($response->getContent(), true);

        if (isset($data['success']) && $data['success']) {
            $this->info($data['message']);
            if (isset($data['data']['details'])) {
                foreach ($data['data']['details'] as $detail) {
                    $status = $detail['status'];
                    $icon = $status === 'Success' ? '✅' : '❌';
                    $this->line("{$icon} Project #{$detail['id']} ({$detail['name']}): {$status}");
                }
            }
        } else {
            $this->error($data['message'] ?? 'An unknown error occurred.');
        }

        $this->info('Synchronization process finished.');
    }
}
