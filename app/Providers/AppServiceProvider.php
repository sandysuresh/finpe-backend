<?php

namespace App\Providers;

use App\Services\Aeps\AepsProvider;
use App\Services\Aeps\VimoPayAepsAdapter;
use App\Services\Payout\PayoutMasterProvider;
use App\Services\Payout\PayoutProvider;
use App\Services\Payout\VimoPayPayoutMaster;
use App\Services\Payout\VimoPayPayoutTransfer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AepsProvider::class, VimoPayAepsAdapter::class);
        $this->app->bind(PayoutMasterProvider::class, VimoPayPayoutMaster::class);
        $this->app->bind(PayoutProvider::class, VimoPayPayoutTransfer::class);
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
