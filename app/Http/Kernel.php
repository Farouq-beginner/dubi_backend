<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class, // Biasanya tidak perlu untuk API murni
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class, // Penting untuk API
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [ // Middleware untuk rute web (jika Anda pakai)
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class, // Jarang dipakai API
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class, // Jarang dipakai API
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [ // <-- GRUP PENTING UNTUK API KITA
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // Hanya jika pakai stateful auth (SPA)
            'throttle:api', // Pembatasan request
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // Pastikan 'auth:sanctum' TIDAK ADA DI SINI, tapi di routes/api.php
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     * Alias ini yang membuat 'auth:sanctum' dan 'can:ability' berfungsi.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [ // Atau '$routeMiddleware' di versi lama
        'auth' => \App\Http\Middleware\Authenticate::class, // <-- Alias untuk 'auth'
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class, // <-- Alias untuk 'can' (Gate/Policy)
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}