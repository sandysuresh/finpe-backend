<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
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
        if (! is_array($value)) {
            return is_string($value) ? mb_substr($value, 0, 2000) : $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            $name = strtolower((string) $key);
            if (in_array($name, $this->sensitiveKeys(), true) || str_contains($name, 'secret') || str_contains($name, 'password')) {
                $out[$key] = '[redacted]';
                continue;
            }
            if (in_array($name, ['account_number', 'account'], true) && is_string($item) && strlen($item) > 4) {
                $out[$key] = str_repeat('*', max(0, strlen($item) - 4)).substr($item, -4);
                continue;
            }
            $out[$key] = $this->redact($item);
        }

        return $out;
    }
}
