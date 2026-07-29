<?php

use App\Http\Middleware\AdminOrOwner;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\OwnerOnly;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'owner' => OwnerOnly::class,
            'admin-or-owner' => AdminOrOwner::class,
            'permission' => CheckPermission::class,
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
