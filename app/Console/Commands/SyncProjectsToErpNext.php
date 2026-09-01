<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\SyncLog;
use App\Services\FrappeAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncProjectsToErpNext extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'projects:sync-to-erpnext 
                            {project_id? : ID of project to sync} 
                            {--force : Force re-sync of already synced projects} 
                            {--limit=50 : Maximum number of projects to sync in one run} 
                            {--status=* : Specific statuses to sync (default: in_execution,implementation,approved)}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically sync execution-phase projects to ERPNext';

    protected $frappeService;

    public function __construct(FrappeAPIService $frappeService)
    {
        parent::__construct();
        $this->frappeService = $frappeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting ERPNext Project Sync...');
        Log::info('=== ERPNext Project Sync Started ===');

        $force = $this->option('force');
        $limit = (int) $this->option('limit');
        $statuses = $this->option('status') ?: ['in_execution', 'implementation', 'approved'];
        $projectId = $this->argument('project_id');

        // Build query
        $query = Project::with([
            'detail', 'cost', 'financings', 'locations',
            'executiveActivities.actions', 'mainObjectives', 'specialObjectives', 'supervisingAuthorities',
        ]);

        if ($projectId) {
            $query->where('id', $projectId);
        } else {
            $query->whereIn('status', $statuses);
            if (! $force) {
                $query->where(function ($q) {
                    $q->whereNull('erpnext_project_id')
                        ->orWhere('sync_status', 'failed')
                        ->orWhereNull('sync_status');
                });
            }
        }

        $projects = $query->limit($limit)->get();

        if ($projects->isEmpty()) {
            $this->info('✅ No projects to sync.');
            Log::info('No projects found to sync', [
                'force' => $force,
                'statuses' => $statuses,
                'limit' => $limit,
                'project_id' => $projectId,
            ]);

            return 0;
        }

        $this->info("📊 Found {$projects->count()} project(s) to sync");
        Log::info('Found projects to sync', ['count' => $projects->count()]);

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($projects->count());
        $progressBar->start();

        foreach ($projects as $project) {
            try {
                $result = $this->syncProject($project, $force);

                if ($result['status'] === 'success') {
                    $successCount++;
                } elseif ($result['status'] === 'skipped') {
                    $skippedCount++;
                } else {
                    $failCount++;
                }

            } catch (\Exception $e) {
                $failCount++;
                $this->logSyncFailure($project, $e);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Sync Complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Failed', $failCount],
                ['Skipped', $skippedCount],
                ['Total', $projects->count()],
            ]
        );

        Log::info('=== ERPNext Project Sync Completed ===', [
            'success' => $successCount,
            'failed' => $failCount,
            'skipped' => $skippedCount,
            'total' => $projects->count(),
        ]);

        return $failCount > 0 ? 1 : 0;
    }

    /**
     * Sync a single project to ERPNext.
     */
    protected function syncProject(Project $project, bool $force): array
    {
        $projectId = $project->id;
        $projectName = $project->project_name;

        Log::info('Syncing project to ERPNext', [
            'project_id' => $projectId,
            'project_name' => $projectName,
            'current_status' => $project->status,
            'force' => $force,
        ]);

        // Check if already synced
        if ($project->erpnext_project_id && ! $force) {
            Log::info('Project already synced, skipping', [
                'project_id' => $projectId,
                'erpnext_id' => $project->erpnext_project_id,
            ]);

            return ['status' => 'skipped', 'message' => 'Already synced'];
        }

        DB::beginTransaction();

        try {
            // Send to ERPNext with history
            $response = $this->frappeService->sendProjectOnExecution($project, true);

            $erpnextProjectId = $response['data']['name'] ?? $response['name'] ?? null;

            if (! $erpnextProjectId) {
                throw new \Exception('No ERPNext project ID returned');
            }

            // Update project
            $project->update([
                'erpnext_project_id' => $erpnextProjectId,
                'frappe_project_id' => $erpnextProjectId,
                'frappe_project_name' => $response['data']['project_name'] ?? $projectName,
                'sync_status' => 'synced',
                'frappe_sync_status' => 'success',
                'synced_to_erpnext_at' => now(),
                'frappe_synced_at' => now(),
            ]);

            // Log success
            SyncLog::create([
                'syncable_type' => Project::class,
                'syncable_id' => $projectId,
                'sync_type' => 'automatic_batch_sync',
                'status' => 'success',
                'message' => 'Project synced to ERPNext successfully',
                'response_data' => [
                    'erpnext_id' => $erpnextProjectId,
                    'response' => $response,
                    'force' => $force,
                ],
            ]);

            Log::info('✅ Project synced successfully', [
                'project_id' => $projectId,
                'project_name' => $projectName,
                'erpnext_id' => $erpnextProjectId,
            ]);

            DB::commit();

            return [
                'status' => 'success',
                'erpnext_id' => $erpnextProjectId,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            // Update project with failure
            $project->update([
                'sync_status' => 'failed',
                'frappe_sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            // Log failure
            $this->logSyncFailure($project, $e);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log a failed sync to both SyncLog and Laravel log.
     */
    protected function logSyncFailure(Project $project, \Exception $e): void
    {
        Log::error('❌ Project sync failed', [
            'project_id' => $project->id,
            'project_name' => $project->project_name,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        SyncLog::create([
            'syncable_type' => Project::class,
            'syncable_id' => $project->id,
            'sync_type' => 'automatic_batch_sync',
            'status' => 'failed',
            'message' => 'Project sync failed: '.$e->getMessage(),
            'response_data' => [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ],
        ]);
    }
}
