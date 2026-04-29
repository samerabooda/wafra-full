<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware {
    public function handle(Request $request, Closure $next): Response {
        $user = $request->user();
        if ($user && $user->must_change_pass && !$request->is('api/auth/change-password')) {
            return response()->json([
                'success'          => false,
                'message'          => 'You must change your password before continuing.',
                'must_change_pass' => true,
            ],403);
        }
        return $next($request);
    }
}
