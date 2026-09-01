<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only log successful or noteworthy requests
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            // Check if it's a view action (exclude assets, AJAX, audit-logs themselves)
            if (! $request->expectsJson() &&
                ! str_contains($request->url(), '/storage/') &&
                ! str_contains($request->path(), 'audit-logs')) {
                AuditLogService::log(
                    action: 'view',
                    description: 'Viewed page: '.$request->path()
                );
            }
        } elseif (! $request->isMethod('GET') && $response->isSuccessful()) {
            // POST, PUT, DELETE are usually handled by model events,
            // but we can log them here if they don't trigger model events (e.g., custom actions)
            // AuditLogService::log(action: strtolower($request->method()), description: 'Performed ' . $request->method() . ' action');
        }
    }
}
