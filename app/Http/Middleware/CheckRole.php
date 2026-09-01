<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            // التحقق من وجود مستخدم مسجل الدخول
            if (! auth()->check()) {
                return redirect()->route('login');
            }

            $user = auth()->user();

            // التحقق من حالة الحساب
            if ($user->status === 'Disabled') {
                auth()->logout();

                // Use session flash instead of writing to log if disk is full
                flash()->error('حسابك معطل. يرجى التواصل مع المسؤول.');

                return redirect()->route('login');
            }

            // التحقق من الصلاحيات
            if ($user->hasAnyRole($roles)) {
                return $next($request);
            }

            // للطلبات API
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحيات كافية للوصول إلى هذا المورد.',
                ], 403);
            }

            // للطلبات العادية
            flash()->error('ليس لديك صلاحيات كافية للوصول إلى هذه الصفحة.');

            return redirect()->back()->withInput();

        } catch (\Exception $e) {
            // Log error only if we have disk space
            try {
                Log::error('CheckRole Middleware Error: '.$e->getMessage(), [
                    'user_id' => auth()->id(),
                    'url' => $request->fullUrl(),
                    'roles' => $roles,
                ]);
            } catch (\Exception $logException) {
                // If logging fails (disk full), just continue without logging
                // We can use session flash as alternative
                flash()->warning('حدث خطأ في النظام. يرجى المحاولة مرة أخرى.');
            }

            // Return generic error response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في النظام. يرجى المحاولة مرة أخرى.',
                ], 500);
            }

            flash()->error('حدث خطأ في النظام. يرجى المحاولة مرة أخرى.');

            return redirect()->back();
        }
    }
}
