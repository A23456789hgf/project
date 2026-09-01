<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\SyncLog;
use App\Services\FrappeAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProjectToErpNext implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $project;

    protected $isAutomated;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Project $project, bool $isAutomated = true)
    {
        $this->project = $project;
        $this->isAutomated = $isAutomated;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FrappeAPIService $frappeService)
    {
        $project = $this->project;

        Log::info('Processing SyncProjectToErpNext Job', ['project_id' => $project->id]);

        try {
            // Update project sync status to 'pending' if it was failed,
            // though usually it might be better to leave it as is until success.
            // For background jobs, we might want to let the user know it's being processed if we had a "Processing" status.

            $frappeResult = $frappeService->sendProjectOnExecution($project, true);

            $frappeProjectId = $frappeResult['data']['name'] ?? $frappeResult['name'] ?? null;

            // Update project with sync info
            $project->update([
                'erpnext_project_id' => $frappeProjectId,
                'frappe_project_id' => $frappeProjectId,
                'frappe_project_name' => $frappeResult['data']['project_name'] ?? null,
                'sync_status' => 'synced',
                'frappe_sync_status' => 'success',
                'synced_to_erpnext_at' => now(),
                'frappe_synced_at' => now(),
                'execution_started_at' => $project->execution_started_at ?? now(),
            ]);

            // Log success to SyncLog
            SyncLog::create([
                'syncable_type' => Project::class,
                'syncable_id' => $project->id,
                'sync_type' => $this->isAutomated ? 'approval_automated_sync' : 'manual_sync',
                'status' => 'success',
                'message' => 'Project synced to ERPNext via background job',
                'response_data' => $frappeResult,
            ]);

            Log::info('Job: Project successfully synced to ERPNext', [
                'project_id' => $project->id,
                'erpnext_project_id' => $frappeProjectId,
            ]);

        } catch (\Exception $e) {
            // Update as failed
            $project->update([
                'sync_status' => 'failed',
                'frappe_sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            SyncLog::create([
                'syncable_type' => Project::class,
                'syncable_id' => $project->id,
                'sync_type' => $this->isAutomated ? 'approval_automated_sync' : 'manual_sync',
                'status' => 'failed',
                'message' => 'Background sync failed: '.$e->getMessage(),
                'response_data' => ['error' => $e->getMessage()],
            ]);

            Log::error('Job: Failed to sync project to ERPNext', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw exception to allow Queue retry mechanisms to work if configured
            // Or suppress if we want to handle failure gracefully without retries
            // For now, we suppress and just log failure as per original controller logic
        }
    }
}
