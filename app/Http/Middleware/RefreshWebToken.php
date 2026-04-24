<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RefreshWebToken
{
    /**
     * Refresh the Sanctum token on each authenticated request.
     * The old token is deleted and a new one is issued, returned
     * via the `X-New-Token` response header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::guard('sanctum')->user();

        if ($user && $request->bearerToken()) {
            $currentToken = $user->currentAccessToken();

            if ($currentToken) {
                $abilities = $currentToken->abilities ?? ['*'];
                $currentToken->delete();

                $newToken = $user->createToken('web-token', $abilities)->plainTextToken;
                $response->headers->set('X-New-Token', $newToken);
            }
        }

        return $response;
    }
}