<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use App\Services\Aeps\AepsPayloadSanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogVendorApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $response = $next($request);
        $vendor = $request->attributes->get('apiVendor');

        if ($vendor) {
            ApiLog::create([
                'vendor_id' => $vendor->id,
                'method' => $request->method(),
                'endpoint' => '/'.$request->path(),
                'status_code' => $response->getStatusCode(),
                'request_payload' => $this->redact($request->except($this->sensitiveKeys())),
                'response_payload' => $this->redact(json_decode($response->getContent(), true) ?? []),
                'ip_address' => $request->ip(),
                'response_time_ms' => (int) round((microtime(true) - $started) * 1000),
            ]);
        }

        return $response;
    }

    private function sensitiveKeys(): array
    {
        return [
            'password', 'secret', 'api_secret', 'secret_key', 'api_key',
            'username', 'authorization', 'token',
        ];
    }

    private function redact(mixed $value): mixed
    {
        return AepsPayloadSanitizer::redact($value);
    }
}
