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
        // same-origin-allow-popups allows OAuth popups while still protecting
        // against Spectre-style cross-origin leaks
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        // cross-origin allows CDN resources to load without CORP headers on their end
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');

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

        // NOTE: Cross-Origin-Embedder-Policy: require-corp is intentionally
        // NOT set here. It would block all cross-origin resources (CDN fonts,
        // avatars, analytics) that don't include CORP headers, causing
        // ERR_BLOCKED_BY_RESPONSE.NotSameOriginAfterDefaultedToSameOriginByCoep.
        // Re-enable only if you need SharedArrayBuffer and can ensure all
        // cross-origin resources send the required CORP header.
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
            // Cloudflare Insights (static.cloudflareinsights.com) added for analytics.
            // unpkg.com used by MathLive math equation editor.
            // www.google.com + www.gstatic.com: Google reCAPTCHA v3 scripts
            // www.gstatic.com/firebasejs: Firebase JS SDK (ESM modules)
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' "
                . "https://cdn.jsdelivr.net "
                . "https://cdnjs.cloudflare.com "
                . "https://cdn.tailwindcss.com "
                . "https://static.cloudflareinsights.com "
                . "https://unpkg.com "
                . "https://www.google.com "
                . "https://www.gstatic.com",

            // Styles — unsafe-inline needed by inline-styled components
            // unpkg.com used by MathLive static stylesheet.
            // www.gstatic.com: reCAPTCHA widget styles
            "style-src 'self' 'unsafe-inline' "
                . "https://cdn.jsdelivr.net "
                . "https://cdnjs.cloudflare.com "
                . "https://fonts.googleapis.com "
                . "https://unpkg.com "
                . "https://www.gstatic.com",

            // Web fonts
            "font-src 'self' data: "
                . "https://fonts.gstatic.com "
                . "https://cdn.jsdelivr.net",

            // Images — data: for base64 inline; blob: for canvas exports
            // ui-avatars.com serves generated initials avatars used across the app
            // www.gstatic.com: reCAPTCHA badge image
            "img-src 'self' data: blob: "
                . "https://ui-avatars.com "
                . "https://api.qrserver.com "
                . "https://cdn.jsdelivr.net "
                . "https://www.gstatic.com "
                . "https://www.google.com",

            // XHR / fetch / WebSocket
            // cdn.jsdelivr.net needed for sourcemap fetches by browser DevTools
            // cloudflareinsights.com needed for analytics beacon
            // www.google.com + www.gstatic.com: reCAPTCHA API + Firebase token verification
            // fcm.googleapis.com + firebaseinstallations.googleapis.com: Firebase SDK
            "connect-src 'self' "
                . "https://cdn.jsdelivr.net "
                . "https://cloudflareinsights.com "
                . "https://static.cloudflareinsights.com "
                . "https://www.google.com "
                . "https://www.gstatic.com "
                . "https://recaptchaenterprise.googleapis.com "
                . "https://fcm.googleapis.com "
                . "https://firebase.googleapis.com "
                . "https://firebaseinstallations.googleapis.com "
                . "https://identitytoolkit.googleapis.com "
                . "https://securetoken.googleapis.com",

            // No plugins (Flash, Silverlight, etc.)
            "object-src 'none'",

            // No media unless self-hosted
            "media-src 'self'",

            // No web workers except from same origin
            "worker-src 'self' blob:",

            // Allow iframes for interactive tools (Desmos, GeoGebra, Excalidraw, YouTube)
            // www.google.com: reCAPTCHA v3 challenge iframe
            "frame-src 'self' "
                . "https://www.google.com "
                . "https://www.desmos.com "
                . "https://*.desmos.com "
                . "https://www.geogebra.org "
                . "https://*.geogebra.org "
                . "https://excalidraw.com "
                . "https://*.excalidraw.com "
                . "https://www.youtube.com "
                . "https://www.youtube-nocookie.com",

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
