<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add browser-enforced protections to every web response.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->add([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ]);

        // The Vite dev server injects inline scripts and uses another origin,
        // so the policy only applies to compiled assets.
        if (! Vite::isRunningHot()) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Pages rendered for a signed-in user must not be restored from the
        // browser cache after logout.
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }

    protected function contentSecurityPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            // Alpine.js evaluates its inline expressions with the Function
            // constructor, which needs 'unsafe-eval'. Inline <script> blocks
            // and inline event handlers stay blocked.
            "script-src 'self' 'unsafe-eval'",
            "style-src 'self' https://fonts.bunny.net",
            // Alpine toggles visibility through style attributes.
            "style-src-attr 'unsafe-inline'",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data:",
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
    }
}
