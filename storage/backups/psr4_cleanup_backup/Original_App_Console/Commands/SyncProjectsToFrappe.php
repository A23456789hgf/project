<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\FrappeAPIService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncProjectsToFrappe extends Command
{
    protected $signature = 'frappe:sync-projects 
                            {--status= : Filter projects by status (e.g. execution, in_progress)}
                            {--failed : Retry previously failed synced projects}
                            {--force : Force resync of already synced projects} 
                            {--dry-run : Show what would be synced without actually syncing}';

    protected $description = 'Automatically POST projects to Frappe API. Syncs execution projects, new projects and retries failed ones.';

    protected $frappeService;

    private $stats = [
        'total_found' => 0,
        'successful' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function __construct(FrappeAPIService $frappeService)
    {
        parent::__construct();
        $this->frappeService = $frappeService;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting Frappe Project Sync...');
        $this->newLine();

        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $status = $this->option('status');
        $failed = $this->option('failed');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No projects will be synced');
            $this->newLine();
        }

        if (! $this->verifyFrappeConnection()) {
            $reason = $this->frappeService->getLastError() ?? 'Unknown connection error';
            $this->error('❌ Cannot connect to Frappe API. Please check configuration.');
            $this->error("📍 Reason: {$reason}");
            Log::error("Frappe sync failed: API connection error. Reason: {$reason}");

            return 1;
        }

        $this->info('✅ Frappe API connection verified');
        $this->newLine();

        $this->syncProjects($status, $failed, $force, $dryRun);

        $this->displaySummary();
        $this->logResults();

        return $this->stats['failed'] > 0 ? 1 : 0;
    }

    private function verifyFrappeConnection(): bool
    {
        return $this->frappeService->testConnection();
    }

    private function syncProjects($status, bool $failed, bool $force, bool $dryRun): void
    {
        $query = Project::with(['detail', 'cost', 'financings']); // Load relations needed

        if ($status) {
            if ($status === 'execution') {
                $status = 'in_execution';
            }
            $query->where('status', $status);
        } else {
            // General sync for both in progress and execution
            $query->whereIn('status', ['in_progress', 'in_execution']);
        }

        if ($failed) {
            $query->where(function ($q) {
                $q->where('sync_status', 'failed')
                    ->orWhere('frappe_sync_status', 'failed')
                    ->orWhereNotNull('frappe_sync_error');
            });
        } elseif (! $force) {
            $query->where(function ($q) {
                $q->where('frappe_synced', false)
                    ->orWhereNull('frappe_synced_at')
                    ->orWhereNull('erpnext_project_id');
            });
        }

        $projects = $query->get();
        $this->stats['total_found'] = $projects->count();

        if ($this->stats['total_found'] === 0) {
            $this->info('No projects to sync found.');

            return;
        }

        $this->info("Found {$this->stats['total_found']} project(s) to sync");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($this->stats['total_found']);
        $progressBar->start();

        foreach ($projects as $project) {
            try {
                DB::beginTransaction();
                $this->syncSingleProject($project, $dryRun);
                DB::commit();
                $this->stats['successful']++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->stats['failed']++;
                $this->stats['errors'][] = [
                    'project_id' => $project->id,
                    'project_name' => $project->project_name,
                    'error' => $e->getMessage(),
                ];
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function syncSingleProject(Project $project, bool $dryRun): void
    {
        $this->line('', 'info');
        $this->info("📋 Syncing: {$project->project_name} (ID: {$project->id}, Status: {$project->status})");

        if ($dryRun) {
            $this->line('   [DRY RUN] Would sync this project');

            return;
        }

        $project->update([
            'frappe_last_sync_attempt' => Carbon::now(),
            'frappe_sync_attempts' => $project->frappe_sync_attempts + 1,
        ]);

        if (in_array($project->status, ['in_execution', 'execution'])) {
            try {
                $result = $this->frappeService->sendProjectOnExecution($project, true);
                $frappeProjectId = $result['data']['name'] ?? $result['name'] ?? null;

                $project->update([
                    'frappe_synced_at' => now(),
                    'frappe_synced' => true,
                    'frappe_sync_at' => now(),
                    'frappe_project_id' => $frappeProjectId,
                    'frappe_project_name' => $result['data']['project_name'] ?? null,
                    'frappe_sync_status' => 'success',
                    'erpnext_project_id' => $frappeProjectId,
                    'sync_status' => 'synced',
                    'synced_to_erpnext_at' => now(),
                    'frappe_sync_error' => null,
                    'sync_error' => null,
                    'execution_started_at' => $project->execution_started_at ?? now(),
                ]);

                $this->line('   ✅ Synced successfully (Execution Phase)');
                $this->line("   Frappe ID: {$frappeProjectId}", 'info');

                Log::info('Project synced to Frappe successfully (Execution)', [
                    'project_id' => $project->id,
                    'frappe_id' => $frappeProjectId,
                    'attempt' => $project->frappe_sync_attempts,
                ]);
            } catch (\Exception $e) {
                $this->handleSyncFailure($project, $e->getMessage());
            }

        } else {
            $result = $this->frappeService->postProjectToFrappe($project);

            if ($result['success']) {
                $frappeId = $result['frappe_id'] ?? null;
                $project->update([
                    'frappe_synced' => true,
                    'frappe_sync_at' => Carbon::now(),
                    'frappe_project_id' => $frappeId,
                    'erpnext_project_id' => $frappeId,
                    'frappe_sync_error' => null,
                    'sync_status' => 'synced',
                ]);

                $this->line('   ✅ Synced successfully');
                $this->line("   Frappe ID: {$frappeId}", 'info');

                Log::info('Project synced to Frappe successfully', [
                    'project_id' => $project->id,
                    'frappe_id' => $frappeId,
                    'attempt' => $project->frappe_sync_attempts,
                ]);
            } else {
                $this->handleSyncFailure($project, $result['message'] ?? 'Unknown error', $result['error'] ?? null);
            }
        }
    }

    private function handleSyncFailure(Project $project, string $errorMsg, $responseData = null): void
    {
        $project->update([
            'frappe_sync_error' => $errorMsg,
            'sync_error' => $errorMsg,
            'frappe_sync_status' => 'failed',
            'sync_status' => 'failed',
        ]);

        $this->line("   ❌ Sync failed: {$errorMsg}", 'error');

        Log::error('Project sync to Frappe failed', [
            'project_id' => $project->id,
            'project_name' => $project->project_name,
            'attempt' => $project->frappe_sync_attempts,
            'error' => $errorMsg,
            'response' => $responseData,
        ]);

        throw new \Exception($errorMsg);
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Sync Summary:');
        $this->line('─────────────────────────────────────');
        $this->line("Total Found:     {$this->stats['total_found']}");
        $this->line("<info>✅ Successful:    {$this->stats['successful']}</info>");
        $this->line("<error>❌ Failed:        {$this->stats['failed']}</error>");
        $this->line("⏭️  Skipped:       {$this->stats['skipped']}");
        $this->line('─────────────────────────────────────');

        if (count($this->stats['errors']) > 0) {
            $this->newLine();
            $this->error('Failed Projects:');
            foreach ($this->stats['errors'] as $error) {
                $this->line("  • {$error['project_name']} (ID: {$error['project_id']})");
                $this->line("    Error: {$error['error']}", 'info');
            }
        }
    }

    private function logResults(): void
    {
        $summary = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'command' => 'frappe:sync-projects',
            'total_found' => $this->stats['total_found'],
            'successful' => $this->stats['successful'],
            'failed' => $this->stats['failed'],
            'errors' => $this->stats['errors'],
        ];

        Log::channel('frappe_sync')->info('Frappe Sync Command Completed', $summary);
    }
}
