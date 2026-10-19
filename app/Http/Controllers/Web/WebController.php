<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{User, CommissionCard, ManagerInvite, UserPermission};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Validator};

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
        $faExists      = User::where('role', 'finance_admin')->exists();
        $pendingInvites = ManagerInvite::whereNull('used_at')->exists();

        return response()->json([
            'exists'          => $faExists,
            'invites_pending' => $pendingInvites, // show register tab for invited managers
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
    public function cardsIndex()        { return view('cards.list'); }
    public function cardsCreate()       { return view('cards.create'); }
    public function cardsModified(Request $request)
    {
        // Render the modified-accounts table SERVER-SIDE (no dependency on browser JS).
        // Columns: Branch · Edit month · Account · Opening month · BEFORE · Reason · AFTER.
        $user = Auth::user();
        $q = DB::table('card_modifications as m')
            ->join('commission_cards as c', 'm.card_id', '=', 'c.id')
            ->leftJoin('branches as b',  'c.branch_id', '=', 'b.id')
            ->whereNull('c.deleted_at');

        $isFa = method_exists($user, 'isFinanceAdmin') && $user->isFinanceAdmin();
        if (!$isFa && method_exists($user, 'branchScopeId')) {
            $q->where('c.branch_id', $user->branchScopeId());          // branch users: their branch only
        } elseif ($isFa && $request->branch_id) {
            $q->where('c.branch_id', (int) $request->branch_id);        // FA optional branch filter
        }

        $mods = $q->orderByDesc('m.modified_at')->limit(20000)->get([
            'c.account_number', 'c.month',
            'b.name_ar as branch_ar', 'b.name_en as branch_en',
            'm.reason', 'm.modified_at',
            'm.old_data', 'm.new_data',
        ]);

        // Resolve employee IDs → names + format the before/after snapshots into readable lines.
        $emps  = \App\Models\Employee::pluck('name', 'id')->toArray();
        $name  = fn($id) => $id ? ($emps[$id] ?? ('#' . $id)) : '—';
        $money = fn($v) => ($v === null || $v === '') ? '—' : ('$' . rtrim(rtrim(number_format((float) $v, 2), '0'), '.'));
        $kindL = fn($v) => $v === 'new' ? 'جديد' : ($v === 'sub' ? 'فرعي' : ($v ?: '—'));
        $defs  = [
            ['بروكر', 'broker_id', 'broker_commission'],
            ['مسوّق داخلي 1', 'marketer_id', 'marketer_commission'],
            ['مسوّق داخلي 2', 'ext_marketer2_id', 'ext_commission2'],
            ['مسوّق خارجي', 'ext_marketer1_id', 'ext_commission1'],
        ];
        // Show ONLY the fields that actually changed (e.g. broker X → broker Y) — nothing else.
        $build = function ($data, $other) use ($defs, $name, $money, $kindL) {
            $lines = [];
            foreach ($defs as [$lbl, $idK, $comK]) {
                $idv = $data[$idK] ?? null; $com = $data[$comK] ?? null;
                $oId = $other[$idK] ?? null; $oCom = $other[$comK] ?? null;
                if (($idv == $oId) && ((float) $com == (float) $oCom)) continue;   // unchanged → skip
                $lines[] = ['lbl' => $lbl, 'val' => $name($idv) . ' (' . $money($com) . ')', 'changed' => true];
            }
            foreach (['initial_deposit' => 'إيداع الفتح', 'monthly_deposit' => 'إيداع شهري', 'account_kind' => 'النوع', 'parent_account' => 'الحساب الأساسي'] as $k => $lbl) {
                $v = $data[$k] ?? null; $ov = $other[$k] ?? null;
                if ($v == $ov) continue;   // unchanged → skip
                $disp = in_array($k, ['initial_deposit', 'monthly_deposit']) ? $money($v)
                      : ($k === 'account_kind' ? $kindL($v) : ($v === null || $v === '' ? '—' : $v));
                $lines[] = ['lbl' => $lbl, 'val' => $disp, 'changed' => true];
            }
            return $lines;
        };
        foreach ($mods as $r) {
            $old = json_decode($r->old_data ?? '[]', true) ?: [];
            $new = json_decode($r->new_data ?? '[]', true) ?: [];
            $r->before    = $build($old, $new);
            $r->after     = $build($new, $old);
            $r->mod_month = $r->modified_at ? \Carbon\Carbon::parse($r->modified_at)->translatedFormat('M Y') : '—';
        }

        $branches = $isFa
            ? DB::table('branches')->whereNull('deleted_at')->orderBy('name_ar')->get(['id', 'name_ar', 'name_en'])
            : collect();

        return view('cards.modified', [
            'mods'        => $mods,
            'modBranches' => $branches,
            'modBranchId' => $request->branch_id,
            'modIsFa'     => $isFa,
        ]);
    }
    public function cardsSearch()       { return view('cards.search'); }
    public function cardsEditSearch()   { return view('cards.edit'); }
    public function cardsEdit(int $id)  { return view('cards.edit', ['cardId' => $id]); }

    public function cardsTree()
    {
        return view('cards.tree');
    }

    // ── Reports ────────────────────────────────────────────────
    public function reports()              { return view('reports.index'); }
    public function reportsTable()         { return view('reports.table'); }
    public function reportsDynamic()       { return view('reports.dynamic'); }
    public function reportsBranchMonthly(Request $request)
    {
        // Server-rendered: cards OPENED or MODIFIED in the selected month (branch-scoped).
        $user   = Auth::user();
        $isFa   = method_exists($user, 'isFinanceAdmin') && $user->isFinanceAdmin();
        $scoped = method_exists($user, 'isScopedToBranch') && $user->isScopedToBranch();
        $m = $request->query('m'); $y = $request->query('y');
        $branchId = $request->query('branch_id');
        $monthStr = ($m && $y) ? ($m . ' ' . $y) : null;

        $rows = collect();
        if ($monthStr) {
            $q = DB::table('commission_cards as c')
                ->leftJoin('branches as b',  'c.branch_id', '=', 'b.id')
                ->leftJoin('employees as bk', 'c.broker_id', '=', 'bk.id')
                ->leftJoin('employees as m1', 'c.marketer_id', '=', 'm1.id')
                ->leftJoin('employees as m2', 'c.ext_marketer2_id', '=', 'm2.id')
                ->leftJoin('referrers as rf', 'rf.account_number', '=', 'c.referral_account')
                ->whereNull('c.deleted_at')
                // Real commission cards only — exclude in-flight Call-Center leads (drafts / awaiting /
                // accepted / rejected) exactly like the cards list and dashboard do, so they never
                // leak into the branch report.
                ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
                ->where(function ($w) use ($monthStr) {
                    $w->where('c.month', $monthStr)->orWhere('c.modified_month', $monthStr);
                });
            if ($scoped)                $q->where('c.branch_id', $user->branchScopeId());
            elseif ($isFa && $branchId) $q->where('c.branch_id', (int) $branchId);

            $rows = $q->orderBy('c.account_number')->limit(20000)->get([
                'c.account_number', 'c.month', 'c.modified_month', 'c.status', 'c.account_kind', 'c.parent_account',
                'c.initial_deposit',
                'b.name_ar as branch_ar', 'b.name_en as branch_en',
                'bk.name as broker_name', 'c.broker_commission',
                'm1.name as m1_name', 'c.marketer_commission',
                'm2.name as m2_name', 'c.ext_commission2',
                'c.referral_account', 'c.referral_commission',
                'rf.name as referral_name', 'rf.type as referral_type',
            ]);
        }

        $branches = $isFa ? DB::table('branches')->whereNull('deleted_at')->orderBy('name_ar')->get(['id', 'name_ar', 'name_en']) : collect();

        return view('reports.branch-monthly', [
            'bmRows'      => $rows,
            'bmBranches'  => $branches,
            'bmM'         => $m,
            'bmY'         => $y,
            'bmBranchId'  => $branchId,
            'bmIsFa'      => $isFa,
            'bmMonthStr'  => $monthStr,
        ]);
    }

    // ── Referrals report (المحيلون) — rendered server-side, branch-scoped ──
    // A "referrer" is an account number that appears in other cards' referral_account.
    public function reportsReferrals(Request $request)
    {
        $user   = Auth::user();
        $isFa   = method_exists($user, 'isFinanceAdmin') && $user->isFinanceAdmin();
        $scoped = method_exists($user, 'isScopedToBranch') && $user->isScopedToBranch();
        $branchId = $request->branch_id;

        // Fresh base query each call (whereNotNull referral + branch scope).
        $base = function () use ($scoped, $isFa, $user, $branchId) {
            $q = DB::table('commission_cards as c')->whereNull('c.deleted_at')
                // Exclude in-flight CC leads — only real opened accounts count as referrals.
                ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
                ->whereNotNull('c.referral_account')->where('c.referral_account', '!=', '');
            if ($scoped)                       $q->where('c.branch_id', $user->branchScopeId());
            elseif ($isFa && $branchId)        $q->where('c.branch_id', (int) $branchId);
            return $q;
        };

        // KPIs (referral commission intentionally dropped — it isn't a real figure)
        $tot = $base()->selectRaw('COUNT(*) total, COUNT(DISTINCT c.referral_account) referrers')->first();

        // Referrers leaderboard — accounts brought + #branches + per-account commission range (high/low)
        $referrers = $base()
            ->select('c.referral_account as ref')
            ->selectRaw('COUNT(*) accounts, COUNT(DISTINCT c.branch_id) branches, '
                . 'MAX(c.referral_commission) maxc, MIN(NULLIF(c.referral_commission,0)) minc')
            ->groupBy('c.referral_account')
            ->orderByDesc('accounts')
            ->limit(2000)->get();

        // Per-branch referral activity (best branch with referrers)
        $byBranch = $base()->leftJoin('branches as b', 'c.branch_id', '=', 'b.id')
            ->selectRaw('COALESCE(b.name_ar,"غير محدد") nar, COUNT(*) referrals, COUNT(DISTINCT c.referral_account) referrers')
            ->groupBy('b.name_ar')
            ->orderByDesc('referrals')->get();

        // Monthly referral trend (counts only)
        $byMonth = $base()->select('c.month')
            ->selectRaw('MAX(c.month_date) md, COUNT(*) referrals')
            ->groupBy('c.month')->orderByRaw('MAX(c.month_date)')->get();

        $branches = $isFa ? DB::table('branches')->whereNull('deleted_at')->orderBy('name_ar')->get(['id', 'name_ar', 'name_en']) : collect();

        // Attach each referrer's saved name + TYPE (client / employee). The type lives on the
        // referrer ACCOUNT (referrers table), so classifying it once applies to all its cards.
        // Unclassified = client by default ("clients until proven otherwise").
        $saved = collect();
        try { $saved = DB::table('referrers')->get()->keyBy('account_number'); } catch (\Throwable $e) {}
        $unnamed = 0; $empCount = 0; $cliCount = 0;
        foreach ($referrers as $r) {
            $row = $saved[$r->ref] ?? null;
            $r->name        = $row->name ?? null;
            $r->ref_type    = ($row && ($row->type ?? null) === 'employee') ? 'employee' : 'client';
            $r->employee_id = $row->employee_id ?? null;
            if (!$r->name) $unnamed++;
            if ($r->ref_type === 'employee') $empCount++; else $cliCount++;
        }
        $rfNameList = $saved->pluck('name')->filter()->unique()->values()->sort()->values();

        return view('reports.referrals', [
            'rfTot'      => $tot,
            'rfList'     => $referrers,
            'rfByBranch' => $byBranch,
            'rfByMonth'  => $byMonth,
            'rfBranches' => $branches,
            'rfIsFa'     => $isFa,
            'rfBranchId' => $branchId,
            'rfUnnamed'  => $unnamed,
            'rfNameList' => $rfNameList,
            'rfEmpCount' => $empCount,
            'rfCliCount' => $cliCount,
        ]);
    }

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

    // ── Guide ──────────────────────────────────────────────────
    public function guide()
    {
        return view('guide.page');
    }

    public function profile()
    {
        return view('profile.index');
    }

    public function notificationsTracker()
    {
        abort_unless(auth()->user()?->isFinanceAdmin(), 403);
        return view('notifications.tracker');
    }

    public function notificationsInbox()
    {
        return view('notifications.inbox');
    }

    // ── Call Center ────────────────────────────────────────────
    public function callcenterIndex()
    {
        // Full CC hub (create/send cards) — Call-Center branch staff + Finance Admin only.
        // Regular branch managers are redirected to their incoming-cards view.
        $u = auth()->user();
        if ($u && !$u->isCallCenterStaff()) {
            return $u->isScopedToBranch()
                ? redirect()->route('callcenter.pending')
                : abort(403);
        }
        return view('callcenter.index');
    }

    public function callcenterPending()
    {
        return view('callcenter.pending');
    }

    // Shared "العملاء قيد المتابعة" — branch managers, Call-Center staff, Finance Admin.
    public function callcenterFollowup()
    {
        return view('callcenter.followup');
    }
}
