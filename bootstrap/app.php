<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request) {
            $path = trim($request->path(), '/');
            $referer = (string) $request->headers->get('referer', '');
            $vendorRequest = $path === 'vendor'
                || str_starts_with($path, 'vendor/')
                || str_contains($referer, '/vendor/');

            return $vendorRequest
                ? route('vendor.login')
                : route('admin.login');
        });
        $middleware->trustHosts();
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\LogPanelActivity::class);
        $middleware->alias([
            'auth.vendor' => \App\Http\Middleware\AuthenticateVendor::class,
            'admin.module' => \App\Http\Middleware\EnsureAdminModule::class,
            'vendor.api' => \App\Http\Middleware\AuthenticateVendorApi::class,
            'vendor.provider' => \App\Http\Middleware\EnsureAssignedPayoutProvider::class,
            'vendor.api.log' => \App\Http\Middleware\LogVendorApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
