<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     * Runs on every request (web + API).
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

        // Module 22 — Security headers on every response (CSP, HSTS, X-Frame, etc.)
        \App\Http\Middleware\SecurityHeaders::class,

        // Low-Internet & Fast Speed Optimization: Gzip HTTP compression
        \App\Http\Middleware\CompressResponse::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // AuthenticateSession invalidates sessions when the password changes
            // Uncomment once fully tested with the password policy flow:
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // Module 22 — Idle session timeout (reads timeout from settings table)
            \App\Http\Middleware\SessionTimeout::class,

            // Multi-tenancy: resolve + bind current school context (TenantScope depends on this)
            \App\Http\Middleware\SetSchoolContext::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // Multi-tenancy: resolve + bind current school context (TenantScope depends on this)
            \App\Http\Middleware\SetSchoolContext::class,
        ],
    ];

    /**
     * The application's route middleware.
     * May be assigned to groups or used individually on routes.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // Laravel built-ins
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        // Module 1 — Email verification gate
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Module 2 — Spatie role/permission middleware
        'role'               => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,

        // Module 22 — 2FA session enforcement (web)
        'two_factor'         => \App\Http\Middleware\EnsureTwoFactorEnabled::class,

        // Module 22 — Password policy: force-change on next login flag
        'password.policy'    => \App\Http\Middleware\EnforcePasswordPolicy::class,

        // Module 22 — File upload MIME/extension allowlist + AV scan hook
        'validate.upload'    => \App\Http\Middleware\ValidateFileUpload::class,

        // Module 22 — Row-level security: scope API queries to owned student IDs
        'scope.own'          => \App\Http\Middleware\ScopeToOwnData::class,

        // Module 1 — Multi-tenancy: attach school_id context from Sanctum token / session
        'school.context'     => \App\Http\Middleware\SetSchoolContext::class,
    ];
}
