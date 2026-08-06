<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds HTTP security headers to every response.
 *
 * Registered globally in Kernel.php $middleware stack so it covers
 * both web and API responses.
 *
 * ─── CSP notes ────────────────────────────────────────────────────────────
 * 'unsafe-inline' / 'unsafe-eval' are required by several third-party
 * UI libraries (ApexCharts, Tiptap, FullCalendar inline styles, etc.).
 * When those libraries are updated / bundled properly these directives
 * should be removed and replaced with nonces.
 *
 * Nonce-based CSP upgrade path:
 *   1. Generate a per-request nonce:  $nonce = base64_encode(random_bytes(16))
 *   2. Share it with Blade:           View::share('cspNonce', $nonce)
 *   3. Add to CSP:                    "script-src 'self' 'nonce-{$nonce}'"
 *   4. Use in Blade:                  <script nonce="{{ $cspNonce }}">
 * ─────────────────────────────────────────────────────────────────────────
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        $this->applyCommonHeaders($response);

        // Full browser-security headers only for non-API responses
        if (! $request->is('api/*')) {
            $this->applyWebHeaders($response, $request);
        }

        return $response;
    }

    // -----------------------------------------------------------------------

    /**
     * Headers applied to ALL responses (web + API).
     */
    private function applyCommonHeaders(Response $response): void
    {
        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Legacy clickjacking protection (redundant with CSP frame-ancestors, kept for compatibility)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Legacy XSS filter — still respected by some older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information leaked to third parties
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser feature APIs (camera, mic, geolocation, etc.)
        $response->headers->set('Permissions-Policy', implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]));

        // Cross-Origin isolation headers
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // HSTS — browsers ignore over HTTP; safe to always send
        // preload requires registration at https://hstspreload.org/
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload',
        );

        // Remove server fingerprint headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
    }

    /**
     * Browser-specific headers for web (non-API) responses.
     */
    private function applyWebHeaders(Response $response, Request $request): void
    {
        $response->headers->set('Content-Security-Policy', $this->buildCsp());

        // Required for SharedArrayBuffer / high-resolution timers (cross-origin isolation)
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
    }

    /**
     * Build the Content-Security-Policy header value.
     *
     * CDNs used by this app:
     *   - cdn.jsdelivr.net      — Bootstrap, Alpine.js, ApexCharts, etc.
     *   - cdnjs.cloudflare.com  — various UI libs
     *   - fonts.googleapis.com  — Google Fonts CSS
     *   - fonts.gstatic.com     — Google Fonts files
     *   - ui-avatars.com        — generated user avatars
     *   - api.qrserver.com      — QR code images for 2FA / ID cards
     */
    private function buildCsp(): string
    {
        $directives = [
            // Fallback for unspecified fetch directives
            "default-src 'self'",

            // Scripts — unsafe-inline/eval needed by ApexCharts, Tiptap, etc.
            // TODO: replace with nonces when libraries are bundled
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' "
                . "https://cdn.jsdelivr.net "
                . "https://cdnjs.cloudflare.com "
                . "https://cdn.tailwindcss.com",

            // Styles — unsafe-inline needed by inline-styled components
            "style-src 'self' 'unsafe-inline' "
                . "https://cdn.jsdelivr.net "
                . "https://cdnjs.cloudflare.com "
                . "https://fonts.googleapis.com",

            // Web fonts
            "font-src 'self' data: "
                . "https://fonts.gstatic.com "
                . "https://cdn.jsdelivr.net",

            // Images — data: for base64 inline; blob: for canvas exports
            "img-src 'self' data: blob: "
                . "https://ui-avatars.com "
                . "https://api.qrserver.com",

            // XHR / fetch / WebSocket
            "connect-src 'self'",

            // No plugins (Flash, Silverlight, etc.)
            "object-src 'none'",

            // No media unless self-hosted
            "media-src 'self'",

            // No web workers except from same origin
            "worker-src 'self' blob:",

            // No iframes (this app does not embed external frames)
            "frame-src 'none'",

            // Prevent this app from being framed by others
            "frame-ancestors 'none'",

            // Restrict <base> tag to same origin
            "base-uri 'self'",

            // Restrict form submissions to same origin
            "form-action 'self'",

            // Upgrade http:// sub-resource requests to https://
            "upgrade-insecure-requests",
        ];

        return implode('; ', $directives);
    }
}
