<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-store');
        } else {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; ".
                "base-uri 'self'; ".
                "form-action 'self'; ".
                "frame-ancestors 'none'; ".
                "object-src 'none'; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
                "font-src 'self' https://fonts.gstatic.com data:; ".
                "img-src 'self' data: blob:; ".
                "connect-src 'self'"
            );
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
