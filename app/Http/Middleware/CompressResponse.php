<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompressResponse
{
    /**
     * Handle an incoming request.
     * Compresses HTML, JSON, and text responses over 1KB using Gzip
     * to ensure ultra-fast loading over low internet / 2G / 3G / spotty Wi-Fi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!function_exists('gzencode')) {
            return $response;
        }

        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (strpos($acceptEncoding, 'gzip') === false) {
            return $response;
        }

        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        $isCompressible = empty($contentType) ||
            strpos($contentType, 'text/') !== false ||
            strpos($contentType, 'application/json') !== false ||
            strpos($contentType, 'application/javascript') !== false ||
            strpos($contentType, 'application/xml') !== false;

        if (!$isCompressible) {
            return $response;
        }

        $content = $response->getContent();
        if (is_string($content) && strlen($content) > 1024) {
            $compressed = gzencode($content, 6);
            if ($compressed !== false && strlen($compressed) < strlen($content)) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', (string)strlen($compressed));
                $response->headers->set('Vary', 'Accept-Encoding');
            }
        }

        return $response;
    }
}
