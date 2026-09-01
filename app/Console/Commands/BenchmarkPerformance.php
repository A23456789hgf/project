<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BenchmarkPerformance extends Command
{
    protected $signature = 'benchmark:run';

    protected $description = 'Benchmark core application routes';

    public function handle()
    {
        $user = User::withoutGlobalScopes()->first();
        if (! $user) {
            $this->error('No users found.');

            return;
        }

        Auth::login($user);

        $routes = [
            '/' => 'GET',
            '/dashboard' => 'GET',
            '/projects' => 'GET',
            '/projects/reports' => 'GET',
            '/projects/reports/status' => 'GET',
            '/projects/reports/implementation' => 'GET',
            '/projects/reports/quality' => 'GET',
        ];

        $results = [];

        foreach ($routes as $uri => $method) {
            $this->info("Testing {$uri}...");

            \DB::flushQueryLog();
            \DB::enableQueryLog();

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $request = Request::create($uri, $method);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // Handle through Http Kernel
            $kernel = app()->make(Kernel::class);
            $response = $kernel->handle($request);

            $executionTime = microtime(true) - $startTime;
            $queries = \DB::getQueryLog();
            $queryCount = count($queries);

            $queryTime = 0;
            foreach ($queries as $query) {
                $queryTime += $query['time'];
            }

            $memory = (memory_get_usage(true) - $startMemory) / 1024 / 1024;
            $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;

            $results[] = [
                'URL' => $uri,
                'Status' => $response->status(),
                'Time (s)' => round($executionTime, 4),
                'Queries' => $queryCount,
                'Q Time (ms)' => round($queryTime, 2),
                'Peak Mem (MB)' => round($peakMemory, 2),
            ];

            $kernel->terminate($request, $response);
        }

        $this->table(['URL', 'Status', 'Time (s)', 'Queries', 'Q Time (ms)', 'Peak Mem (MB)'], $results);
    }
}
