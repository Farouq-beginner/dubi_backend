<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- Pastikan ini ada
use Illuminate\Auth\AuthenticationException; // <-- Tambahkan ini

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Pastikan ini aktif
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->validateCsrfTokens(except: [
        //     'stripe/*', // Contoh jika Anda pakai web
        // ]);

         // Pastikan alias middleware 'auth' dan 'can' ada (biasanya otomatis)
         // $middleware->alias([
         //     'auth' => \App\Http\Middleware\Authenticate::class,
         //     'can' => \Illuminate\Auth\Middleware\Authorize::class,
         // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // --- TAMBAHKAN BLOK INI ---
        // Handler khusus untuk API yang tidak terautentikasi
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            // Jika request adalah API (ke '/api/*' atau mengharapkan JSON)
            if ($request->is('api/*') || $request->expectsJson()) {
                // Kembalikan respons JSON 401, JANGAN redirect
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            // Biarkan handler default (redirect ke login) berjalan untuk request web
        });
        // ---------------------------

        // (Mungkin ada handler exception lain di sini)

    })->create();