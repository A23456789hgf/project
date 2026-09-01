<?php

namespace App\Console\Commands;

use App\Http\Controllers\Project\Services\ProjectExportService;
use App\Http\Controllers\Project\Services\ProjectService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BenchmarkPerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:performance {--test=all : Specific test to run (listing, export-comp, export-hier, sync-entities, sync-perms, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark database queries, memory consumption, and execution time for Phase 1 profiling';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Performance & Memory Profiling...');

        // Ensure we have an authenticated user for permission/scoping checks
        $user = User::first();
        if ($user) {
            auth()->login($user);
            $this->info("👤 Authenticated as user ID: {$user->id} ({$user->name})");
        } else {
            $this->warn('⚠️ No user found in database. Running without auth user.');
        }

        $test = $this->option('test');
        $results = [];

        if ($test === 'all' || $test === 'listing') {
            $results[] = $this->benchmarkListing();
        }

        if ($test === 'all' || $test === 'export-comp') {
            $results[] = $this->benchmarkComprehensiveExport();
        }

        if ($test === 'all' || $test === 'export-hier') {
            $results[] = $this->benchmarkHierarchicalExport();
        }

        if ($test === 'all' || $test === 'sync-entities') {
            $results[] = $this->benchmarkSyncEntities();
        }

        if ($test === 'all' || $test === 'sync-perms') {
            $results[] = $this->benchmarkSyncPerms();
        }

        $this->newLine();
        $this->info('📊 Benchmark Results Summary:');

        $tableData = array_map(function ($r) {
            return [
                $r['name'],
                $r['queries'],
                number_format($r['time_ms'], 2).' ms',
                number_format($r['peak_memory_mb'], 2).' MB',
                number_format($r['memory_delta_mb'], 2).' MB',
                $r['status'],
            ];
        }, $results);

        $this->table(
            ['Test Name', 'Query Count', 'Execution Time', 'Peak Memory', 'Memory Delta', 'Status'],
            $tableData
        );

        // Save results to file for comparison
        $logPath = storage_path('logs/benchmark_'.date('Y_m_d_H_i_s').'.json');
        File::ensureDirectoryExists(dirname($logPath));
        File::put($logPath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("💾 Results saved to: {$logPath}");

        return 0;
    }

    protected function runBenchmark(string $name, callable $callback): array
    {
        $this->info("⏳ Running benchmark: {$name}...");

        // Force garbage collection before measuring
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        $startMem = memory_get_usage(true);
        $startTime = microtime(true);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $status = 'Success';
        $errorMessage = null;

        try {
            $callback();
        } catch (\Exception $e) {
            $status = 'Failed: '.$e->getMessage();
            $this->error("❌ Error in {$name}: ".$e->getMessage());
        }

        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $endTime = microtime(true);
        $endMem = memory_get_usage(true);
        $peakMem = memory_get_peak_usage(true);

        $timeMs = ($endTime - $startTime) * 1000;
        $deltaMb = ($endMem - $startMem) / 1024 / 1024;
        $peakMb = $peakMem / 1024 / 1024;

        return [
            'name' => $name,
            'queries' => $queries,
            'time_ms' => $timeMs,
            'peak_memory_mb' => $peakMb,
            'memory_delta_mb' => $deltaMb,
            'status' => $status,
        ];
    }

    protected function benchmarkListing(): array
    {
        return $this->runBenchmark('Project Listing (getProjects + Appends)', function () {
            $service = app(ProjectService::class);
            $request = new Request;
            $projects = $service->getProjects($request);

            // Simulate serialization/view rendering where model accessors and appends are triggered
            if ($projects instanceof LengthAwarePaginator || $projects instanceof Collection) {
                foreach ($projects as $project) {
                    // Trigger accessors and appends (e.g., mainObjective, draft_integrity)
                    $project->toArray();
                }
            }
        });
    }

    protected function benchmarkComprehensiveExport(): array
    {
        return $this->runBenchmark('Comprehensive Excel Export', function () {
            $service = app(ProjectExportService::class);
            $request = new Request;

            // Execute export (builds spreadsheet in memory before returning StreamedResponse)
            $response = $service->exportAllProjectsExcelComprehensive($request);
        });
    }

    protected function benchmarkHierarchicalExport(): array
    {
        return $this->runBenchmark('Hierarchical Excel Export', function () {
            $service = app(ProjectExportService::class);
            $request = new Request;

            $response = $service->exportAllProjectsExcel($request);
        });
    }

    protected function benchmarkSyncEntities(): array
    {
        return $this->runBenchmark('Sync Project Entities Command', function () {
            Artisan::call('projects:sync-entities');
        });
    }

    protected function benchmarkSyncPerms(): array
    {
        return $this->runBenchmark('Permissions Matrix DB Sync', function () {
            Artisan::call('permissions:matrix-sync', ['--sync-db' => true]);
        });
    }
}
