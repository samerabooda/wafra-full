<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Password, Hash, Validator};
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    // ── 1. Send Reset Link ─────────────────────────────────────
    public function sendResetLink(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'تم إرسال رابط استعادة كلمة المرور على بريدك الإلكتروني.');
        }

        $msg = match ($status) {
            Password::INVALID_USER    => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني.',
            Password::RESET_THROTTLED => 'يرجى الانتظار قليلاً قبل إعادة المحاولة.',
            default                   => 'حدث خطأ، يرجى المحاولة مجدداً.',
        };

        return back()->withErrors(['email' => $msg])->withInput();
    }

    // ── 2. Show Reset Form ─────────────────────────────────────
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // ── 3. Process New Password ────────────────────────────────
    public function reset(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.min'       => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.confirmed' => 'كلمة المرور وتأكيدها غير متطابقتين.',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'must_change_pass' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('auth.login')
                ->with('status', 'تم تغيير كلمة المرور بنجاح. يمكنك الدخول الآن.');
        }

        $msg = match ($status) {
            Password::INVALID_TOKEN => 'رابط الاستعادة منتهي الصلاحية أو غير صالح. يرجى طلب رابط جديد.',
            Password::INVALID_USER  => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني.',
            default                 => 'حدث خطأ، يرجى المحاولة مجدداً.',
        };

        return back()->withErrors(['email' => $msg]);
    }
}
