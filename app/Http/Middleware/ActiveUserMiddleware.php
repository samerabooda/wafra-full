<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware {
    public function handle(Request $request, Closure $next): Response {
        $user = $request->user();
        if ($user && !$user->is_active) {
            return response()->json(['success'=>false,'message'=>'Account deactivated.'],403);
        }
        return $next($request);
    }
}
