<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            // Allow only logout and password change routes to prevent redirect loops
            $allowedRoutes = [
                'password.change.forced',
                'password.change.update',
                'logout',
                'dashboard',
                'profile.signature.setup',
            ];

            $routeName = $request->route() ? $request->route()->getName() : null;

            if (! in_array($routeName, $allowedRoutes) && ! $request->is('force-change-password') && ! $request->is('logout')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'يجب تغيير كلمة المرور الافتراضية قبل استخدام النظام.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }

                flash()->warning('يجب تغيير كلمة المرور الافتراضية أولاً قبل تصفح النظام.');

                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
