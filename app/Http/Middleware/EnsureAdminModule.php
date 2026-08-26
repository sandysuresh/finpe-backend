<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if ($admin->status !== 'active') {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'This account is inactive.',
            ]);
        }

        $admin->loadMissing('modulePermissions');

        if (! $admin->hasModule($module)) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
