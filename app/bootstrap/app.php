<?php

use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckMainModulePermission;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckRoleOrPermission;
use App\Http\Middleware\EnforcePasswordChange;
use App\Http\Middleware\EnsureRoutePermission;
use App\Http\Middleware\EnsureSignatureSetup;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetAppUrlByRequest;
use App\Http\Middleware\SuperAdminAccess;
use App\Http\Middleware\ValidationErrorsToFlasher;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'auth.session' => AuthenticateSession::class,
            'cache.headers' => SetCacheHeaders::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => RequirePassword::class,
            'precognitive' => HandlePrecognitiveRequests::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'require_signature' => EnsureSignatureSetup::class,
            'role' => CheckRole::class,
            'role_id' => CheckRole::class,
            'permission' => CheckPermission::class,
            'role_or_permission' => CheckRoleOrPermission::class,
            'superadmin' => SuperAdminAccess::class,
            'auto_permission' => EnsureRoutePermission::class,
            'set_app_url' => SetAppUrlByRequest::class,
        ]);

        $middleware->web(append: [
            CheckMainModulePermission::class,
            AuditMiddleware::class,
            EnsureRoutePermission::class,
            SetAppUrlByRequest::class,
            EnforcePasswordChange::class,
            ValidationErrorsToFlasher::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
