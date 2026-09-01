<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSignatureSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasSignature()) {
            if ($request->expectsJson() || $request->ajax() || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'التوقيع الإلكتروني مطلوب لإتمام هذه العملية.',
                    'require_signature' => true,
                ], 403);
            }

            return redirect()->back()->with('require_signature', true)->with('error', 'يجب إعداد التوقيع الإلكتروني الخاص بك قبل إتمام هذه العملية.');
        }

        return $next($request);
    }
}
