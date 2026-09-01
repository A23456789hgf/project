<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (TokenMismatchException $e, $request) {
            if (auth()->check()) {
                return redirect()->route('dashboard');
            }

            return redirect()->route('login')->withErrors(['session' => 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى.']);
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            \Log::error('AuthorizationException 403 URL: '.$request->fullUrl().' Message: '.$e->getMessage().' Trace: '.$e->getTraceAsString());
        });

        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            \Log::error('AccessDeniedHttpException 403 URL: '.$request->fullUrl().' Message: '.$e->getMessage().' Trace: '.$e->getTraceAsString());
        });
    }
}
