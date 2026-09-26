<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to protect against common web attacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable XSS filter in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy - send origin only for cross-origin requests
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy - restrict browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
