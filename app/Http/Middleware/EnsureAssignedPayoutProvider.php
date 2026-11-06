<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use App\Support\AssignedPayoutProviders;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAssignedPayoutProvider
{
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        $vendor = $request->user('vendor') ?? $request->attributes->get('apiVendor');
        $allowed = $vendor instanceof Vendor && (
            $provider === 'any'
                ? AssignedPayoutProviders::codes($vendor) !== []
                : AssignedPayoutProviders::allows($vendor, $provider)
        );

        if ($allowed) {
            return $next($request);
        }

        $message = $provider === 'any'
            ? 'No payout vendor is assigned to your account.'
            : 'This payout vendor is not assigned to your account.';

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        abort(403, $message);
    }
}
