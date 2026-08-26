<?php

namespace App\Http\Middleware;

use App\Models\ApiCredential;
use App\Support\VendorApiSecurity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateVendorApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->headers->has('X-API-Secret') || $request->filled('secret_key') || $request->filled('api_secret')) {
            return $this->deny($request, 400, 'Do not send the API secret. Sign the request with HMAC-SHA256 instead.');
        }

        if (strlen((string) $request->getContent()) > 65536) {
            return $this->deny($request, 413, 'Request body is too large.');
        }

        $failKey = 'vendor_api_auth:'.$request->ip();
        if (RateLimiter::tooManyAttempts($failKey, 20)) {
            return $this->deny($request, 429, 'Too many failed authentication attempts. Try again later.');
        }

        $apiKey = trim((string) $request->header('X-API-Key', ''));
        $timestamp = trim((string) $request->header('X-Timestamp', ''));
        $nonce = trim((string) $request->header('X-Nonce', ''));
        $signature = trim((string) $request->header('X-Signature', ''));

        if ($apiKey === '' || $timestamp === '' || $nonce === '' || $signature === '') {
            RateLimiter::hit($failKey, 300);

            return $this->deny($request, 401, 'Authentication failed.');
        }

        if (! ctype_digit($timestamp)) {
            RateLimiter::hit($failKey, 300);

            return $this->deny($request, 401, 'Authentication failed.');
        }

        $skew = abs(time() - (int) $timestamp);
        if ($skew > VendorApiSecurity::TIMESTAMP_WINDOW_SECONDS) {
            RateLimiter::hit($failKey, 300);

            return $this->deny($request, 401, 'Authentication failed.');
        }

        if (! preg_match('/^[A-Za-z0-9._-]{16,64}$/', $nonce)) {
            RateLimiter::hit($failKey, 300);

            return $this->deny($request, 401, 'Authentication failed.');
        }

        $credential = ApiCredential::query()
            ->with('vendor.wallet')
            ->where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        $canonical = VendorApiSecurity::canonicalString($request, $timestamp, $nonce);
        $probeSecret = $credential?->secret_key ?: hash('sha256', 'finpay-dummy-'.$apiKey);
        $expected = VendorApiSecurity::signature($canonical, $probeSecret);

        if (! $credential || ! $credential->secret_key || ! VendorApiSecurity::signaturesMatch($signature, $expected)) {
            RateLimiter::hit($failKey, 300);

            return $this->deny($request, 401, 'Authentication failed.');
        }

        $nonceKey = 'vendor_api_nonce:'.$credential->id.':'.$nonce;
        if (! Cache::add($nonceKey, 1, VendorApiSecurity::TIMESTAMP_WINDOW_SECONDS)) {
            RateLimiter::hit($failKey, 300);

            return $this->deny($request, 401, 'Authentication failed.');
        }

        $whitelist = array_values(array_filter($credential->ip_whitelist ?? []));
        if ($whitelist === [] || ! VendorApiSecurity::ipAllowed((string) $request->ip(), $whitelist)) {
            RateLimiter::hit($failKey, 120);

            return $this->deny($request, 403, 'API access denied.');
        }

        $vendor = $credential->vendor;
        if (! $vendor || $vendor->status !== 'active' || ! $vendor->api_enabled) {
            return $this->deny($request, 403, 'API access denied.');
        }

        RateLimiter::clear($failKey);
        $credential->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('apiVendor', $vendor);
        $request->attributes->set('apiCredential', $credential);

        return $next($request);
    }

    private function deny(Request $request, int $status, string $message): Response
    {
        Log::notice('vendor_api_auth_denied', [
            'status' => $status,
            'ip' => $request->ip(),
            'path' => $request->path(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
