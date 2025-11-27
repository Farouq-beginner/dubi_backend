<?php

namespace App\Http\Middleware; // <--- PERBAIKAN: Tambahkan '\Middleware'

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Jika request adalah API (JSON), jangan redirect (return null)
        // Ini akan memicu Laravel mengirim error 401 Unauthorized JSON
        if ($request->expectsJson()) {
            return null;
        }

        // Jika request Web biasa, redirect ke halaman login
        return route('login');
    }
}