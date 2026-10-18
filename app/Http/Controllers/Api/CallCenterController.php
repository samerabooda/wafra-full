<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CcNotification, ActivityLog, Employee, Branch};
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
            abort(403, 'فقط فرع مركز الاتصال الذي أنشأ الكرت يمكنه تنفيذ هذا الإجراء.');
        }
    }

    private function assertBranchOwnership(CommissionCard $card, Request $request): void
    {
        $user = $request->user();
        if ($user->isFinanceAdmin()) return;
        if ($card->branch_id !== $user->branch_id) {
            abort(403, 'هذا الكرت غير مخصص لفرعك.');
        }
    }

    // ── Find a CC card with a friendly error message ─────────
    private function findCcCard(int $id): array
    {
        $card = CommissionCard::withTrashed()->find($id);
        if (!$card) {
            return [null, response()->json([
                'success' => false,
                'message' => "لم يتم العثور على الكرت رقم {$id}. قد يكون قد حُذف — حدّث الصفحة.",
            ], 404)];
        }
        if ($card->trashed()) {
            return [null, response()->json([
                'success' => false,
                'message' => "الكرت رقم {$id} محذوف.",
            ], 409)];
        }
        return [$card, null];
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

        // Check duplicate — live row blocks; a soft-deleted row is quietly
        // restored and reused so the unique constraint (account_number,month)
        // doesn't crash the insert.
        $existing = CommissionCard::withTrashed()
                                  ->where('account_number', $request->account_number)
                                  ->where('month', $request->month)
                                  ->first();
        if ($existing && $existing->deleted_at === null) {
            return response()->json([
                'success' => false,
                'message' => "الحساب #{$request->account_number} موجود مسبقاً لشهر {$request->month}.",
            ], 409);
        }

        $card = DB::transaction(function () use ($request, $agent, $user, $existing) {
            $data = [
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
            ];

            if ($existing) {
                // Resurrect — this (account_number, month) was previously cancelled
                $existing->restore();
                $existing->update($data);
                $card = $existing->fresh();
            } else {
                $card = CommissionCard::create($data);
            }

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
        [$card, $err] = $this->findCcCard($id);
        if ($err) return $err;
        $this->assertCcOwnership($card, $request);

        if ($card->cc_status !== 'cc_pending') {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن الإرسال — الكرت في حالة '{$card->cc_status}'. للإرسال يجب أن يكون مسوّدة (cc_pending).",
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
        [$card, $err] = $this->findCcCard($id);
        if ($err) return $err;
        $this->assertBranchOwnership($card, $request);

        if ($card->cc_status === 'accepted') {
            return response()->json([
                'success' => true,
                'message' => "الكرت #{$card->account_number} مقبول مسبقاً.",
                'data'    => $card,
            ]);
        }

        if ($card->cc_status !== 'branch_pending') {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن القبول — الكرت في حالة '{$card->cc_status}'. للقبول يجب أن يكون بانتظار الفرع (branch_pending).",
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
        [$card, $err] = $this->findCcCard($id);
        if ($err) return $err;
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

        if ($card->cc_status === 'rejected') {
            return response()->json([
                'success' => true,
                'message' => "الكرت #{$card->account_number} مرفوض مسبقاً.",
                'data'    => $card,
            ]);
        }

        if (!in_array($card->cc_status, ['branch_pending', 'accepted'])) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن الرفض — الكرت في حالة '{$card->cc_status}'. للرفض يجب أن يكون بانتظار الفرع أو مقبولاً.",
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
        [$card, $err] = $this->findCcCard($id);
        if ($err) return $err;
        $this->assertBranchOwnership($card, $request);

        if ($card->cc_status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => "الكرت #{$card->account_number} مكتمل مسبقاً.",
                'data'    => $card,
            ]);
        }

        if ($card->cc_status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => "يجب قبول الكرت أولاً قبل الإكمال (الحالة الحالية: '{$card->cc_status}').",
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
            // Preserve a 'modified' status if the card was edited after
            // acceptance — do NOT reset back to 'new_added' on completion.
            $preserveStatus = in_array($card->status, ['modified', 'inactive']);
            $update = [
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
            ];
            if (!$preserveStatus) {
                $update['status'] = 'new_added';
            }
            $card->update($update);

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
        [$card, $err] = $this->findCcCard($id);
        if ($err) return $err;
        $this->assertCcOwnership($card, $request);

        if ($card->cc_status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن إعادة الإرسال — الكرت في حالة '{$card->cc_status}'. يجب أن يكون مرفوضاً.",
            ], 422);
        }

        $priorReason = $card->cc_rejection_reason;

        DB::transaction(function () use ($card, $request, $priorReason) {
            // Reset any broker/marketer data the branch may have filled in
            // during a previous acceptance before rejecting — the branch
            // should see a clean form on resend.
            $card->update([
                'cc_status'           => 'branch_pending',
                'cc_rejection_reason' => null,
                'broker_id'           => null,
                'broker_commission'   => 0,
                'marketer_id'         => null,
                'marketer_commission' => 0,
                'ext_marketer1_id'    => null,
                'ext_commission1'     => 0,
                'ext_marketer2_id'    => null,
                'ext_commission2'     => 0,
                'initial_deposit'     => 0,
                'monthly_deposit'     => 0,
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

            ActivityLog::record('cc_card_resent', $card, [
                'to_branch'       => $card->branch_id,
                'prior_rejection' => $priorReason,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "✅ تمت إعادة إرسال الكرت #{$card->account_number} للفرع.",
        ]);
    }

    // ── DELETE /api/cc/cards/{id} — CC Manager cancels a draft ─
    public function cancel(Request $request, int $id): JsonResponse
    {
        [$card, $err] = $this->findCcCard($id);
        if ($err) return $err;
        $this->assertCcOwnership($card, $request);

        if (!in_array($card->cc_status, ['cc_pending', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن إلغاء كرت بحالة '{$card->cc_status}'. للإلغاء يجب أن يكون مسوّدة أو مرفوض.",
            ], 422);
        }

        $accountNumber = $card->account_number;
        $priorStatus   = $card->cc_status;

        DB::transaction(function () use ($card, $request, $priorStatus) {
            ActivityLog::record('cc_card_cancelled', $card, [
                'prior_status'    => $priorStatus,
                'prior_rejection' => $card->cc_rejection_reason,
            ]);
            $card->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "🗑️ تم إلغاء الكرت #{$accountNumber}.",
        ]);
    }

    // ── POST /api/cc/cards/{id}/restore — undo a cancellation ──
    public function restoreCancelled(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::withTrashed()->find($id);
        if (!$card) {
            return response()->json(['success' => false, 'message' => 'Card not found.'], 404);
        }
        if ($card->deleted_at === null) {
            return response()->json(['success' => false, 'message' => 'Card is not cancelled.'], 422);
        }
        $this->assertCcOwnership($card, $request);

        // Make sure (account_number, month) is still free — someone may have
        // created a new live card for the same slot in the meantime.
        $clash = CommissionCard::where('account_number', $card->account_number)
                               ->where('month', $card->month)
                               ->exists();
        if ($clash) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن استعادة الكرت — يوجد كرت نشط بنفس الرقم والشهر.",
            ], 409);
        }

        $card->restore();
        ActivityLog::record('cc_card_restored', $card, []);

        return response()->json([
            'success' => true,
            'message' => "✅ تم استعادة الكرت #{$card->account_number}.",
            'data'    => $card->fresh(),
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
    // Supports filters: cc_status, branch_id, cc_agent_id, month, search, from, to, include_cancelled
    // Supports pagination: per_page (default 100, max 2000)
    public function sent(Request $request): JsonResponse
    {
        $user  = $request->user();

        // ── Base query ───────────────────────────────────────
        // When include_cancelled=1 we also return soft-deleted CC cards so the
        // user can see history / restore them.
        $base = $request->boolean('include_cancelled')
            ? CommissionCard::withTrashed()
            : CommissionCard::query();

        $query = $base->with(['ccAgent:id,name,role', 'branch:id,name_ar,name_en', 'ccBranch:id,name_ar,name_en'])
                      ->whereNotNull('cc_branch_id');

        // Branch scoping — a CC manager sees only their own branch's sent cards
        if ($user->isScopedToBranch()) {
            $query->where('cc_branch_id', $user->branch_id);
        }

        // Filters
        if ($s  = $request->cc_status)   $query->where('cc_status', $s);
        if ($b  = $request->branch_id)   $query->where('branch_id', (int)$b);
        if ($a  = $request->cc_agent_id) $query->where('cc_agent_id', (int)$a);
        if ($m  = $request->month)       $query->where('month', $m);
        if ($f  = $request->from) {
            try { $query->whereDate('month_date', '>=', \Carbon\Carbon::parse('01 ' . $f)); } catch (\Exception $e) {}
        }
        if ($t  = $request->to) {
            try { $query->whereDate('month_date', '<=', \Carbon\Carbon::parse('01 ' . $t)->endOfMonth()); } catch (\Exception $e) {}
        }
        if ($q = $request->search) {
            $query->where(function ($qq) use ($q) {
                $qq->where('account_number', 'like', "%{$q}%")
                   ->orWhere('month', 'like', "%{$q}%")
                   ->orWhere('notes', 'like', "%{$q}%");
            });
        }

        // Summary aggregates for the WHOLE filtered set — not just the current page.
        $sumQ = clone $query;
        $summary = [
            'total'          => (clone $sumQ)->count(),
            'cc_pending'     => (clone $sumQ)->where('cc_status', 'cc_pending')->count(),
            'branch_pending' => (clone $sumQ)->where('cc_status', 'branch_pending')->count(),
            'accepted'       => (clone $sumQ)->where('cc_status', 'accepted')->count(),
            'completed'      => (clone $sumQ)->where('cc_status', 'completed')->count(),
            'rejected'       => (clone $sumQ)->where('cc_status', 'rejected')->count(),
            'cancelled'      => $request->boolean('include_cancelled')
                                ? (clone $sumQ)->whereNotNull('deleted_at')->count()
                                : 0,
        ];

        $perPage = min((int)($request->per_page ?? 100), 2000);
        $cards   = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data'    => $cards,
        ]);
    }

    // ── GET /api/cc/report — Aggregated CC report (server-side) ─
    // Avoids pulling thousands of rows down just to count them client-side.
    public function report(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::whereNotNull('cc_branch_id');

        if ($user->isScopedToBranch()) {
            $query->where('cc_branch_id', $user->branch_id);
        }

        if ($m = $request->month) $query->where('month', $m);
        if ($f = $request->from) {
            try { $query->whereDate('month_date', '>=', \Carbon\Carbon::parse('01 ' . $f)); } catch (\Exception $e) {}
        }
        if ($t = $request->to) {
            try { $query->whereDate('month_date', '<=', \Carbon\Carbon::parse('01 ' . $t)->endOfMonth()); } catch (\Exception $e) {}
        }

        // ── Status counts (one query) ────────────────────────
        $statusRows = (clone $query)
            ->selectRaw('cc_status, COUNT(*) as cnt')
            ->groupBy('cc_status')
            ->pluck('cnt', 'cc_status');

        $kpis = [
            'total'          => (int) $statusRows->sum(),
            'cc_pending'     => (int) ($statusRows['cc_pending']     ?? 0),
            'branch_pending' => (int) ($statusRows['branch_pending'] ?? 0),
            'accepted'       => (int) ($statusRows['accepted']       ?? 0),
            'completed'      => (int) ($statusRows['completed']      ?? 0),
            'rejected'       => (int) ($statusRows['rejected']       ?? 0),
        ];

        // ── By target branch ──────────────────────────────────
        $byBranch = (clone $query)
            ->selectRaw('branch_id,
                COUNT(*) as total,
                SUM(CASE WHEN cc_status="accepted"  THEN 1 ELSE 0 END) as acc,
                SUM(CASE WHEN cc_status="completed" THEN 1 ELSE 0 END) as comp,
                SUM(CASE WHEN cc_status="rejected"  THEN 1 ELSE 0 END) as rej')
            ->groupBy('branch_id')
            ->with('branch:id,name_ar,name_en')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'branch_id' => $r->branch_id,
                'name_ar'   => $r->branch?->name_ar ?? '—',
                'name_en'   => $r->branch?->name_en ?? '—',
                'total'     => (int) $r->total,
                'accepted'  => (int) $r->acc,
                'completed' => (int) $r->comp,
                'rejected'  => (int) $r->rej,
            ])
            ->values();

        // ── By CC agent ───────────────────────────────────────
        $byAgent = (clone $query)
            ->selectRaw('cc_agent_id,
                COUNT(*) as total,
                SUM(CASE WHEN cc_status="completed" THEN 1 ELSE 0 END) as comp,
                SUM(CASE WHEN cc_status="rejected"  THEN 1 ELSE 0 END) as rej')
            ->whereNotNull('cc_agent_id')
            ->groupBy('cc_agent_id')
            ->with('ccAgent:id,name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'agent_id'  => $r->cc_agent_id,
                'name'      => $r->ccAgent?->name ?? '—',
                'total'     => (int) $r->total,
                'completed' => (int) $r->comp,
                'rejected'  => (int) $r->rej,
                'rate'      => $r->total > 0 ? round(($r->comp / $r->total) * 100) : 0,
            ])
            ->values();

        // ── Monthly stats (last 24 months) ────────────────────
        $monthly = (clone $query)
            ->selectRaw("DATE_FORMAT(month_date, '%Y-%m') as ym,
                COUNT(*) as total,
                SUM(CASE WHEN cc_status='cc_pending'     THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN cc_status='branch_pending' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN cc_status='accepted'       THEN 1 ELSE 0 END) as acc,
                SUM(CASE WHEN cc_status='completed'      THEN 1 ELSE 0 END) as comp,
                SUM(CASE WHEN cc_status='rejected'       THEN 1 ELSE 0 END) as rej")
            ->whereNotNull('month_date')
            ->where('month_date', '>=', now()->subMonths(24))
            ->groupBy(DB::raw("DATE_FORMAT(month_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(month_date, '%Y-%m')"))
            ->get()
            ->map(fn($r) => [
                'ym'        => $r->ym,
                'total'     => (int) $r->total,
                'draft'     => (int) $r->draft,
                'sent'      => (int) $r->sent,
                'accepted'  => (int) $r->acc,
                'completed' => (int) $r->comp,
                'rejected'  => (int) $r->rej,
            ])
            ->values();

        return response()->json([
            'success'    => true,
            'kpis'       => $kpis,
            'by_branch'  => $byBranch,
            'by_agent'   => $byAgent,
            'monthly'    => $monthly,
        ]);
    }

    // ── GET /api/cc/notifications ──────────────────────────────
    public function notifications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CcNotification::with(['card:id,account_number,month', 'fromBranch:id,name_ar,name_en', 'sentBy:id,name'])
                               ->latest();

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
        $notif = CcNotification::find($id);
        if (!$notif) {
            return response()->json([
                'success' => false,
                'message' => "لم يتم العثور على الإشعار رقم {$id}.",
            ], 404);
        }
        $user = $request->user();

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
