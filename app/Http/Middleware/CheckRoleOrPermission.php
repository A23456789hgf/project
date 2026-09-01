<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roleOrPermission): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $parts = explode('|', $roleOrPermission);

        foreach ($parts as $part) {
            // Check if it's a role or permission
            // This is a simple heuristic: permissions usually have a dot
            if (str_contains($part, '.')) {
                if ($user->hasPermission($part)) {
                    return $next($request);
                }
            } else {
                if ($user->hasRole($part)) {
                    return $next($request);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return abort(403, 'ليس لديك الصلاحيات المطلوبة لهذه العملية');
    }
}
