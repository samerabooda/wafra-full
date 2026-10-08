<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChangeMiddleware {
    public function handle(Request $request, Closure $next): Response {
        $user = $request->user();
        if ($user && $user->must_change_pass && !$request->is('api/auth/change-password')) {
            // JSON / API caller: return structured response so the SPA can prompt
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success'          => false,
                    'message'          => 'You must change your password before continuing.',
                    'must_change_pass' => true,
                ], 403);
            }

            // Web page: log them out and bounce back to login with a flash
            // message so they must use the password-reset flow.
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('auth.login')->withErrors([
                'email' => 'يجب تغيير كلمة المرور قبل المتابعة. استخدم «نسيت كلمة المرور».',
            ]);
        }
        return $next($request);
    }
}
