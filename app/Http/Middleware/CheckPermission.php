<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $permissions  Comma-separated permissions, | for OR logic
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Support for OR logic using |
        if (str_contains($permissions, '|')) {
            $permissionArray = explode('|', $permissions);
            if ($user->hasAnyPermission($permissionArray)) {
                return $next($request);
            }
        } else {
            // Support for AND logic using multiple arguments (comma)
            $permissionArray = explode(',', $permissions);
            if ($user->hasAllPermissions($permissionArray)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized. Lack of required permissions.'], 403);
        }

        return abort(403, 'ليس لديك الصلاحيات المطلوبة لهذه العملية');
    }
}
