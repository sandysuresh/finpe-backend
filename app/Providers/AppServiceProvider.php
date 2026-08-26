<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(8)->by($request->ip().'|'.$email),
            ];
        });

        RateLimiter::for('vendor-api', function (Request $request) {
            $key = trim((string) $request->header('X-API-Key', ''));

            return Limit::perMinute(60)->by($key !== '' ? $key : $request->ip());
        });
    }
}
