<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        DB::enableQueryLog();

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        $queryTime = 0;
        foreach ($queries as $query) {
            $queryTime += $query['time']; // time is in milliseconds
        }

        $memory = memory_get_peak_usage(true) / 1024 / 1024; // MB

        $url = $request->fullUrl();
        $method = $request->method();

        $logData = sprintf(
            '[%s] %s | Time: %.4fs | Queries: %d (%.2fms) | Memory: %.2fMB',
            $method, $url, $executionTime, $queryCount, $queryTime, $memory
        );

        Log::channel('single')->info('PERF_MONITOR: '.$logData);

        file_put_contents(storage_path('logs/performance.log'), $logData.PHP_EOL, FILE_APPEND);

        return $response;
    }
}
