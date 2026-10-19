<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Prevent browsers from serving a STALE cached HTML page after a deploy.
 * Applies only to HTML page responses — assets keep their own caching.
 */
class NoCacheHtml
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Apply to page/redirect responses (Content-Type isn't set yet at this stage,
        // so we exclude by type instead). Skip JSON APIs and file/stream downloads.
        $skip = $response instanceof \Illuminate\Http\JsonResponse
             || $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
             || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse;

        if (!$skip && method_exists($response, 'header')) {
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }

        return $response;
    }
}
