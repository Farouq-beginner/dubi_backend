<?php

namespace App\Http; // Pastikan namespace ini benar

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request; // Pastikan ini ada

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null; // <-- KEMBALIKAN NULL UNTUK API
        }
        return route('login'); // Redirect untuk web
    }
}