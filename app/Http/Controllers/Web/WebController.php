<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{User, CommissionCard};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Validator, Mail, DB};
use App\Mail\PasswordResetMail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WebController extends Controller
{
    // ── Login Page ─────────────────────────────────────────────
    public function loginPage()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // ── Login Submit ───────────────────────────────────────────
    public function loginPost(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $user = User::where('email', $request->email)
                    ->where('is_active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);

        // Store API token in session for Blade views to use
        $token = $user->createToken('web-session')->plainTextToken;
        session(['api_token' => $token]);

        return redirect()->route('dashboard');
    }

    // ── Register Submit (FA first time) ───────────────────────
    public function registerPost(Request $request)
    {
        if (User::where('role', 'finance_admin')->exists()) {
            return back()->withErrors(['email' => 'Finance Admin already exists.']);
        }

        $v = Validator::make($request->all(), [
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'finance_admin',
            'is_active' => true,
        ]);

        Auth::login($user);
        session(['api_token' => $user->createToken('web-session')->plainTextToken]);

        return redirect()->route('dashboard');
    }

    // ── Logout ─────────────────────────────────────────────────
    public function logout(Request $request)
    {
        // Revoke API token
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    // ── FA Check (for login page JS) ──────────────────────────
    public function faCheck(): JsonResponse
    {
        return response()->json([
            'exists' => User::where('role', 'finance_admin')->exists(),
        ]);
    }

    // ── Dashboard ──────────────────────────────────────────────
    public function dashboard()
    {
        return view('dashboard.index', [
            'monthlyData' => [],
        ]);
    }

    // ── Cards ──────────────────────────────────────────────────
    public function cardsIndex()        { return view('cards.index'); }
    public function cardsCreate()       { return view('cards.create'); }
    public function cardsModified()     { return view('cards.modified'); }
    public function cardsEditSearch()   { return view('cards.edit'); }
    public function cardsEdit(int $id)  { return view('cards.edit', ['cardId' => $id]); }

    public function cardsTree()
    {
        return view('cards.tree');
    }

    // ── Reports ────────────────────────────────────────────────
    public function reports()        { return view('reports.index'); }
    public function reportsDynamic()    { return view('reports.dynamic'); }
    public function callcenter()        { return view('callcenter.index'); }
    public function callcenterPending() { return view('callcenter.pending'); }

    // ── Employees ──────────────────────────────────────────────
    public function employees() { return view('employees.index'); }

    // ── Settings ───────────────────────────────────────────────
    public function settings()
    {
        return view('settings.index');
    }

    // ── Import ─────────────────────────────────────────────────
    public function import()
    {
        if (!Auth::user()->isFinanceAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', 'الاستيراد متاح للمدير المالي فقط.');
        }
        return view('import.index');
    }

    // ── Managers ───────────────────────────────────────────────
    public function managers()
    {
        if (!Auth::user()->isFinanceAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
        return view('managers.index');
    }

    // ── Branches ───────────────────────────────────────────────
    public function branches()
    {
        if (!Auth::user()->isFinanceAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
        return view('settings.index');
    }

    // ── Permissions ────────────────────────────────────────────
    public function permissions()
    {
        if (!Auth::user()->isFinanceAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
        return view('permissions.index');
    }

    // ── POST /forgot-password — Send reset email ───────────────
    public function forgotPassword(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        // Always show success (security: don't reveal if email exists)
        $successMsg = 'إذا كان البريد موجوداً في النظام، سيصلك رابط إعادة التعيين خلال دقيقة.';

        $user = User::where('email', $request->email)->where('is_active', true)->first();
        if (!$user) {
            // Return success anyway (don't expose if email exists)
            return back()->with('reset_sent', $successMsg);
        }

        // Generate secure token
        $token = Str::random(64);

        // Store in password_reset_tokens (replace if exists)
        DB::table('password_reset_tokens')->upsert(
            [
                'email'      => $user->email,
                'token'      => hash('sha256', $token),
                'created_at' => now(),
            ],
            ['email'],
            ['token', 'created_at']
        );

        // Build reset URL
        $resetUrl = route('auth.password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        // Send email
        try {
            Mail::to($user->email)->send(
                new PasswordResetMail($resetUrl, $user->name, 60)
            );
        } catch (\Throwable $e) {
            \Log::error('Password reset mail failed', ['email' => $user->email, 'error' => $e->getMessage()]);
            // Still show success (don't reveal system errors)
        }

        return back()->with('reset_sent', $successMsg);
    }

    // ── GET /reset-password — Show reset form ──────────────────
    public function resetPasswordPage(Request $request)
    {
        $token = $request->token;
        $email = $request->email;

        if (!$token || !$email) {
            return redirect()->route('auth.login')->withErrors(['email' => 'رابط إعادة التعيين غير صالح.']);
        }

        // Verify token exists and not expired (60 min)
        $record = DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->first();

        if (!$record) {
            return redirect()->route('auth.login')->withErrors(['email' => 'رابط إعادة التعيين غير صالح أو منتهي.']);
        }

        if (!hash_equals($record->token, hash('sha256', $token))) {
            return redirect()->route('auth.login')->withErrors(['email' => 'رابط إعادة التعيين غير صحيح.']);
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('auth.login')->withErrors(['email' => 'انتهت صلاحية رابط إعادة التعيين. اطلب رابطاً جديداً.']);
        }

        return view('auth.reset-password', compact('token', 'email'));
    }

    // ── POST /reset-password — Process new password ────────────
    public function resetPasswordSubmit(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);
        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        // Verify token
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->first();

        if (!$record || !hash_equals($record->token, hash('sha256', $request->token))) {
            return back()->withErrors(['email' => 'رابط إعادة التعيين غير صالح.'])->withInput();
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('auth.login')->withErrors(['email' => 'انتهت صلاحية الرابط. اطلب رابطاً جديداً.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'المستخدم غير موجود.']);
        }

        $user->update([
            'password'          => Hash::make($request->password),
            'must_change_pass'  => false,
        ]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all existing API tokens (security)
        $user->tokens()->delete();

        return redirect()->route('auth.login')
                         ->with('success', 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
    }

}
