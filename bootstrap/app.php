<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustHosts();
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->alias([
            'auth.vendor' => \App\Http\Middleware\AuthenticateVendor::class,
            'admin.module' => \App\Http\Middleware\EnsureAdminModule::class,
            'vendor.api' => \App\Http\Middleware\AuthenticateVendorApi::class,
            'vendor.api.log' => \App\Http\Middleware\LogVendorApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
