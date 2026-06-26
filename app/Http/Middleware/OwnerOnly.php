<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isOwner()) {
            abort(403, 'Halaman ini hanya untuk owner.');
        }
        return $next($request);
    }
}
