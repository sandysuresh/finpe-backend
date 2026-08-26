<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateVendor
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.login');
        }

        $vendor = Auth::guard('vendor')->user();
        if ($vendor && $vendor->status !== 'active') {
            Auth::guard('vendor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('vendor.login')->withErrors([
                'email' => 'This account is inactive.',
            ]);
        }

        return $next($request);
    }
}
