<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOrOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->isCleaner()) {
            abort(403, 'Akses tidak diizinkan.');
        }
        return $next($request);
    }
}
