<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOrOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }
        return $next($request);
    }
}
