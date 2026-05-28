<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CcNotification, ActivityLog, Employee};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator};

/**
 * CallCenterController — CC ↔ Branch workflow
 *
 * STATUS FLOW:
 *   CC creates card  → cc_status = 'cc_pending'     (draft, branch can't see it)
 *   CC sends card    → cc_status = 'branch_pending' (branch sees it, awaiting decision)
 *   Branch accepts   → cc_status = 'accepted'        (branch fills broker/deposit)
 *   Branch completes → cc_status = 'completed'       (card finalized)
 *   Branch rejects   → cc_status = 'rejected'        (CC notified with reason)
 *
 * COMMISSION RULE (CC cards only):
 *   broker_commission + marketer_commission ≤ $5/lot — HARD LIMIT, card rejected otherwise.
 */
class CallCenterController extends Controller
{
    /** Max combined broker+marketer commission for CC cards ($/lot) */
    const CC_COMM_LIMIT = 5.0;

    // ── Ownership guards ──────────────────────────────────────
    private function assertCcOwnership(CommissionCard $card, Request $request): void
    {
        $user = $request->user();
        if ($user->isFinanceAdmin()) return;
        if ($card->cc_branch_id !== $user->branch_id) {
            abort(403, 'Only the CC branch that created this card can perform this action.');
        }
    }

    private function assertBranchOwnership(CommissionCard $card, Request $request): void
    {
        $user = $request->user();
        if ($user->isFinanceAdmin()) return;
        if ($card->branch_id !== $user->branch_id) {
            abort(403, 'This card is not assigned to your branch.');
        }
    }

    // ── Validate CC commission limit (broker + marketer ≤ $5) ─
    private function validateCcCommissionLimit(float $brokerComm, float $marketerComm): ?array
    {
        $total = $brokerComm + $marketerComm;
        if ($total > self::CC_COMM_LIMIT) {
            return [
                'success'     => false,
                'cc_limit_exceeded' => true,
                'broker_commission'   => $brokerComm,
                'marketer_commission' => $marketerComm,
                'total'       => $total,
                'limit'       => self::CC_COMM_LIMIT,
                'message'     => sprintf(
                    '⛔ عمولة البروكر (%.1f$) + عمولة المسوّق (%.1f$) = %.1f$ تتجاوز الحد المسموح لكروت مركز الاتصال (%.1f$/lot). يرجى تعديل العمولات.',
                    $brokerComm, $marketerComm, $total, self::CC_COMM_LIMIT
                ),
            ];
        }
        return null;
    }

    // ── POST /api/cc/cards — CC creates card (draft) ──────────
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $v = Validator::make($request->all(), [
            'account_number'    => 'required|string|max:30',
            'month'             => 'required|string|max:20',
            'month_date'        => 'required|date',
            'target_branch_id'  => 'required|exists:branches,id',
            'cc_agent_id'       => 'required|exists:employees,id',
            'account_type_id'   => 'nullable|exists:account_types,id',
            'account_status_id' => 'nullable|exists:account_statuses,id',
            'trading_type_id'   => 'nullable|exists:trading_types,id',
            'account_kind'      => 'nullable|in:new,sub',
            'notes'             => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // Verify agent belongs to CC branch
        $agent = Employee::findOrFail($request->cc_agent_id);
        if ($user->isScopedToBranch() && $agent->branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'يجب أن ينتمي الموظف لفرعك.'], 403);
        }

        // Check duplicate
        if (CommissionCard::where('account_number', $request->account_number)
                          ->where('month', $request->month)
                          ->whereNull('deleted_at')->exists()) {
            return response()->json([
                'success' => false,
                'message' => "الحساب #{$request->account_number} موجود مسبقاً لشهر {$request->month}.",
            ], 409);
        }

        $card = DB::transaction(function () use ($request, $agent, $user) {
            $card = CommissionCard::create([
                'account_number'      => $request->account_number,
                'month'               => $request->month,
                'month_date'          => $request->month_date,
                'branch_id'           => $request->target_branch_id,
                'cc_branch_id'        => $user->branch_id,
                'cc_agent_id'         => $agent->id,
                'cc_agent_commission' => $agent->cc_commission ?? 1.00,
                'account_type_id'     => $request->account_type_id,
                'account_status_id'   => $request->account_status_id,
                'trading_type_id'     => $request->trading_type_id,
                'account_kind'        => $request->account_kind ?? 'new',
                'notes'               => $request->notes,
                'cc_status'           => 'cc_pending',
                'status'              => 'new_added',
                'created_by'          => $user->id,
            ]);
            ActivityLog::record('cc_card_created', $card, [
                'agent'         => $agent->name,
                'target_branch' => $request->target_branch_id,
            ]);
            return $card;
        });

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء الكرت #{$card->account_number}. اضغط «إرسال» لإبلاغ الفرع.",
            'data'    => $card->load(['branch']),
        ], 201);
    }

    // ── POST /api/cc/cards/{id}/send — CC sends to branch ──────
    public function send(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertCcOwnership($card, $request);

        if ($card->cc_status !== 'cc_pending') {
            return response()->json([
                'success' => false,
                'message' => "حالة الكرت '{$card->cc_status}' — لا يمكن إرساله مجدداً.",
            ], 422);
        }

        DB::transaction(function () use ($card, $request) {
            $card->update(['cc_status' => 'branch_pending']);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->cc_branch_id,
                'to_branch_id'   => $card->branch_id,
                'sent_by'        => $request->user()->id,
                'type'           => 'card_sent',
                'status'         => 'unread',
                'message'        => "📩 حساب جديد #{$card->account_number} ({$card->month}) وصل من مركز الاتصال — بانتظار قراركم",
            ]);

            ActivityLog::record('cc_card_sent', $card, ['to_branch' => $card->branch_id]);
        });

        return response()->json([
            'success' => true,
            'message' => "✅ تم إرسال الكرت #{$card->account_number} للفرع — بانتظار الرد.",
        ]);
    }

    // ── PUT /api/cc/cards/{id}/accept — Branch accepts ─────────
    public function accept(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertBranchOwnership($card, $request);

        if ($card->cc_status !== 'branch_pending') {
            return response()->json([
                'success' => false,
                'message' => "حالة الكرت '{$card->cc_status}' — لا يمكن قبوله.",
            ], 422);
        }

        DB::transaction(function () use ($card, $request) {
            $card->update(['cc_status' => 'accepted']);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $request->user()->id,
                'responded_by'   => $request->user()->id,
                'type'           => 'card_accepted',
                'status'         => 'unread',
                'message'        => "✅ الفرع قَبِل الحساب #{$card->account_number} ({$card->month}) — جاري استكمال البيانات",
            ]);

            ActivityLog::record('cc_card_accepted', $card);
        });

        return response()->json([
            'success' => true,
            'message' => "تم قبول الكرت. أضف بيانات البروكر والإيداع لإتمامه.",
            'data'    => $card->fresh(['branch']),
        ]);
    }

    // ── PUT /api/cc/cards/{id}/reject — Branch rejects ─────────
    public function reject(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertBranchOwnership($card, $request);

        $v = Validator::make($request->all(), [
            'reason' => 'required|string|min:5|max:500',
        ], [
            'reason.required' => 'يجب كتابة سبب الرفض',
            'reason.min'      => 'سبب الرفض يجب أن يكون 5 أحرف على الأقل',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        if (!in_array($card->cc_status, ['branch_pending', 'accepted'])) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن رفض كرت بحالة '{$card->cc_status}'.",
            ], 422);
        }

        DB::transaction(function () use ($card, $request) {
            $card->update([
                'cc_status'           => 'rejected',
                'cc_rejection_reason' => $request->reason,
            ]);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $request->user()->id,
                'responded_by'   => $request->user()->id,
                'type'           => 'card_rejected',
                'status'         => 'unread',
                'message'        => "❌ الفرع رَفَضَ الحساب #{$card->account_number} — السبب: {$request->reason}",
            ]);

            ActivityLog::record('cc_card_rejected', $card, ['reason' => $request->reason]);
        });

        return response()->json([
            'success' => true,
            'message' => "تم رفض الكرت. تم إشعار مركز الاتصال بالسبب.",
        ]);
    }

    // ── PUT /api/cc/cards/{id}/complete — Branch completes ──────
    // ⚠️ RULE: broker_commission + marketer_commission ≤ $5
    public function complete(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertBranchOwnership($card, $request);

        if ($card->cc_status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => "يجب قبول الكرت أولاً (الحالة الحالية: '{$card->cc_status}').",
            ], 422);
        }

        $v = Validator::make($request->all(), [
            'broker_id'           => 'required|exists:employees,id',
            'broker_commission'   => 'required|numeric|min:0',
            'marketer_id'         => 'nullable|exists:employees,id',
            'marketer_commission' => 'nullable|numeric|min:0',
            'ext_marketer1_id'    => 'nullable|exists:employees,id',
            'ext_commission1'     => 'nullable|numeric|min:0',
            'ext_marketer2_id'    => 'nullable|exists:employees,id',
            'ext_commission2'     => 'nullable|numeric|min:0',
            'initial_deposit'     => 'required|numeric|min:0',
            'monthly_deposit'     => 'nullable|numeric|min:0',
            'forex_commission'    => 'nullable|numeric|min:0',
            'futures_commission'  => 'nullable|numeric|min:0',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // ── ⚠️ CC HARD LIMIT: broker + marketer ≤ $5 ─────────────
        $brokerComm   = (float) $request->broker_commission;
        $marketerComm = (float) ($request->marketer_commission ?? 0);
        $limitError   = $this->validateCcCommissionLimit($brokerComm, $marketerComm);
        if ($limitError) {
            return response()->json($limitError, 422);
        }

        DB::transaction(function () use ($request, $card) {
            $card->update([
                'broker_id'           => $request->broker_id,
                'broker_commission'   => $request->broker_commission,
                'marketer_id'         => $request->marketer_id,
                'marketer_commission' => $request->marketer_commission ?? 0,
                'ext_marketer1_id'    => $request->ext_marketer1_id,
                'ext_commission1'     => $request->ext_commission1 ?? 0,
                'ext_marketer2_id'    => $request->ext_marketer2_id,
                'ext_commission2'     => $request->ext_commission2 ?? 0,
                'initial_deposit'     => $request->initial_deposit,
                'monthly_deposit'     => $request->monthly_deposit ?? 0,
                'forex_commission'    => $request->forex_commission ?? 0,
                'futures_commission'  => $request->futures_commission ?? 0,
                'cc_status'           => 'completed',
                'status'              => 'new_added',
            ]);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $request->user()->id,
                'responded_by'   => $request->user()->id,
                'type'           => 'card_completed',
                'status'         => 'unread',
                'message'        => "🏁 تم إكمال الحساب #{$card->account_number} ({$card->month}) بواسطة الفرع",
            ]);

            ActivityLog::record('cc_card_completed', $card);
        });

        return response()->json([
            'success' => true,
            'message' => "🏁 تم إكمال الكرت #{$card->account_number} بنجاح.",
            'data'    => $card->fresh(),
        ]);
    }

    // ── POST /api/cc/cards/{id}/resend — CC resends rejected card ─
    public function resend(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertCcOwnership($card, $request);

        if ($card->cc_status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن إعادة إرسال كرت بحالة '{$card->cc_status}'. يجب أن يكون مرفوضاً.",
            ], 422);
        }

        DB::transaction(function () use ($card, $request) {
            $card->update([
                'cc_status'           => 'branch_pending',
                'cc_rejection_reason' => null,
            ]);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->cc_branch_id,
                'to_branch_id'   => $card->branch_id,
                'sent_by'        => $request->user()->id,
                'type'           => 'card_sent',
                'status'         => 'unread',
                'message'        => "🔁 إعادة إرسال الحساب #{$card->account_number} ({$card->month}) من مركز الاتصال — بانتظار قراركم",
            ]);

            ActivityLog::record('cc_card_resent', $card, ['to_branch' => $card->branch_id]);
        });

        return response()->json([
            'success' => true,
            'message' => "✅ تمت إعادة إرسال الكرت #{$card->account_number} للفرع.",
        ]);
    }

    // ── GET /api/cc/pending — Branch: cards awaiting action ────
    public function pending(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::with(['ccBranch', 'ccAgent', 'accountType', 'accountStatus'])
                               ->whereNotNull('cc_branch_id')
                               ->whereIn('cc_status', ['branch_pending', 'accepted']);

        if ($user->isScopedToBranch()) {
            $query->where('branch_id', $user->branch_id);
        }

        $cards = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'count'   => $cards->count(),
            'data'    => $cards,
        ]);
    }

    // ── GET /api/cc/sent — CC: my sent cards ───────────────────
    public function sent(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::with(['ccAgent', 'branch'])
                               ->whereNotNull('cc_branch_id');

        if ($user->isScopedToBranch()) {
            $query->where('cc_branch_id', $user->branch_id);
        }

        if ($s = $request->cc_status) {
            $query->where('cc_status', $s);
        }

        $cards = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json(['success' => true, 'data' => $cards]);
    }

    // ── GET /api/cc/notifications ──────────────────────────────
    public function notifications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CcNotification::with(['card', 'fromBranch', 'sentBy'])->latest();

        if ($user->isScopedToBranch()) {
            $query->forBranch($user->branch_id);
        }

        $all    = $query->limit(50)->get();
        $unread = $all->where('status', 'unread')->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unread,
            'data'         => $all,
        ]);
    }

    // ── PUT /api/cc/notifications/{id}/read ───────────────────
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notif = CcNotification::findOrFail($id);
        $user  = $request->user();

        if ($user->isScopedToBranch() && $notif->to_branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية.'], 403);
        }

        $notif->markRead();
        return response()->json(['success' => true]);
    }

    // ── PUT /api/cc/notifications/read-all ────────────────────
    public function markAllRead(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CcNotification::where('status', 'unread');

        if ($user->isScopedToBranch()) {
            $query->where('to_branch_id', $user->branch_id);
        }

        $count = $query->count();
        $query->update(['status' => 'read', 'read_at' => now()]);

        return response()->json(['success' => true, 'marked' => $count]);
    }
}
