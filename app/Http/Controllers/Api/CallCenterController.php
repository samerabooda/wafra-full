<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CcNotification, ActivityLog, Setting, Employee};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator};
use Carbon\Carbon;

/**
 * CallCenterController
 *
 * Handles the full CC → Branch workflow:
 *  POST /api/cc/cards          — CC creates a card (stage 1)
 *  POST /api/cc/cards/{id}/send — CC sends card to branch
 *  PUT  /api/cc/cards/{id}/accept — Branch accepts
 *  PUT  /api/cc/cards/{id}/reject — Branch rejects + reason
 *  GET  /api/cc/pending          — Branch: pending cards from CC
 *  GET  /api/cc/notifications    — Unread notifications
 *  PUT  /api/cc/notifications/{id}/read
 */
class CallCenterController extends Controller
{
    // ── CC creates card (stage 1: only CC fields) ──────────
    // POST /api/cc/cards
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

        // Ensure agent belongs to CC branch
        $agent = Employee::findOrFail($request->cc_agent_id);
        if ($user->isBranchManager() && $agent->branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Agent must belong to your CC branch.'], 403);
        }

        // Check duplicate
        if (CommissionCard::where('account_number', $request->account_number)
                          ->where('month', $request->month)
                          ->whereNull('deleted_at')->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Account #{$request->account_number} already exists for {$request->month}.",
            ], 409);
        }

        $card = DB::transaction(function () use ($request, $agent, $user) {
            $card = CommissionCard::create([
                'account_number'     => $request->account_number,
                'month'              => $request->month,
                'month_date'         => $request->month_date,
                'branch_id'          => $request->target_branch_id, // target branch
                'cc_branch_id'       => $user->branch_id,           // CC branch
                'cc_agent_id'        => $agent->id,
                'cc_agent_commission'=> $agent->cc_commission,       // auto-fill from employee
                'account_type_id'    => $request->account_type_id,
                'account_status_id'  => $request->account_status_id,
                'trading_type_id'    => $request->trading_type_id,
                'account_kind'       => $request->account_kind ?? 'new',
                'notes'              => $request->notes,
                'cc_status'          => 'cc_pending',
                'status'             => 'new_added',
                'created_by'         => $user->id,
            ]);

            ActivityLog::record('cc_card_created', $card, [
                'agent'         => $agent->name,
                'target_branch' => $request->target_branch_id,
            ]);

            return $card;
        });

        return response()->json([
            'success' => true,
            'message' => "Card #{$card->account_number} created. Ready to send to branch.",
            'data'    => $card->load(['ccBranch', 'ccAgent', 'branch']),
        ], 201);
    }

    // ── CC sends card to branch (triggers notification) ────
    // POST /api/cc/cards/{id}/send
    public function send(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $user = $request->user();

        // Only CC branch can send
        if ($card->cc_branch_id !== $user->branch_id && !$user->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the CC branch can send this card.'], 403);
        }

        if ($card->cc_status !== 'cc_pending') {
            return response()->json(['success' => false, 'message' => "Card status is '{$card->cc_status}' — cannot send again."], 422);
        }

        DB::transaction(function () use ($card, $user) {
            $card->update(['cc_status' => 'accepted']); // mark as sent/pending-acceptance

            // Create notification for target branch
            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->cc_branch_id,
                'to_branch_id'   => $card->branch_id,
                'sent_by'        => $user->id,
                'type'           => 'card_sent',
                'status'         => 'unread',
                'message'        => "حساب جديد #{$card->account_number} ({$card->month}) وصل من فرع CC",
            ]);

            ActivityLog::record('cc_card_sent', $card, ['to_branch' => $card->branch_id]);
        });

        return response()->json([
            'success' => true,
            'message' => "Card #{$card->account_number} sent to branch. Awaiting branch to complete data.",
        ]);
    }

    // ── Branch accepts card ────────────────────────────────
    // PUT /api/cc/cards/{id}/accept
    public function accept(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $user = $request->user();

        // Only the target branch manager can accept
        if ($user->isBranchManager() && $card->branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'This card is not assigned to your branch.'], 403);
        }

        if ($card->cc_status !== 'accepted') {
            return response()->json(['success' => false, 'message' => "Card is '{$card->cc_status}' — cannot accept."], 422);
        }

        DB::transaction(function () use ($card, $user) {
            // status stays 'accepted' — branch will now fill broker/marketer/deposits
            // Notify CC branch
            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $user->id,
                'type'           => 'card_accepted',
                'status'         => 'unread',
                'message'        => "الفرع قَبِل الحساب #{$card->account_number} ({$card->month})",
            ]);

            ActivityLog::record('cc_card_accepted', $card);
        });

        return response()->json([
            'success' => true,
            'message' => "Card #{$card->account_number} accepted. Please complete broker, marketer and deposit fields.",
            'data'    => $card->fresh(['ccBranch', 'ccAgent', 'branch', 'broker', 'marketer']),
        ]);
    }

    // ── Branch rejects card ────────────────────────────────
    // PUT /api/cc/cards/{id}/reject
    public function reject(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $user = $request->user();

        if ($user->isBranchManager() && $card->branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'This card is not assigned to your branch.'], 403);
        }

        $v = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        DB::transaction(function () use ($card, $user, $request) {
            $card->update([
                'cc_status'              => 'rejected',
                'cc_rejection_reason'    => $request->reason,
            ]);

            // Notify CC branch
            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $user->id,
                'responded_by'   => $user->id,
                'type'           => 'card_rejected',
                'status'         => 'unread',
                'message'        => "الفرع رَفَضَ الحساب #{$card->account_number} — السبب: {$request->reason}",
            ]);

            ActivityLog::record('cc_card_rejected', $card, ['reason' => $request->reason]);
        });

        return response()->json([
            'success' => true,
            'message' => "Card #{$card->account_number} rejected. Reason sent to CC branch.",
        ]);
    }

    // ── Branch completes card (broker + marketer + deposits) ─
    // PUT /api/cc/cards/{id}/complete
    public function complete(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $user = $request->user();

        if ($user->isBranchManager() && $card->branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'This card is not assigned to your branch.'], 403);
        }

        if (!in_array($card->cc_status, ['accepted'])) {
            return response()->json(['success' => false, 'message' => "Card must be accepted first."], 422);
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
            'monthly_deposit'     => 'required|numeric|min:0',
            'forex_commission'    => 'nullable|numeric|min:0',
            'futures_commission'  => 'nullable|numeric|min:0',
            'reason'              => 'nullable|string|max:200',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // ── Commission limit check ────────────────────────
        $limitEnabled = Setting::commissionLimitEnabled();
        $limitAmount  = Setting::commissionLimitAmount();

        $total = (float) $request->broker_commission
               + (float) ($request->marketer_commission ?? 0)
               + (float) ($request->ext_commission1 ?? 0)
               + (float) ($request->ext_commission2 ?? 0)
               + (float) $card->cc_agent_commission;

        $warningResponse = $this->checkCommissionLimit(
            $total, $limitEnabled, $limitAmount, $request
        );

        if ($warningResponse) {
            return $warningResponse;
        }

        DB::transaction(function () use ($request, $card, $user) {
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
                'monthly_deposit'     => $request->monthly_deposit,
                'forex_commission'    => $request->forex_commission ?? 0,
                'futures_commission'  => $request->futures_commission ?? 0,
                'cc_status'           => 'completed',
                'status'              => 'new_added',
            ]);

            // Notify CC branch that card is complete
            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $user->id,
                'type'           => 'card_completed',
                'status'         => 'unread',
                'message'        => "تم إكمال بيانات الحساب #{$card->account_number} ({$card->month})",
            ]);

            ActivityLog::record('cc_card_completed', $card, [
                'broker'   => $request->broker_id,
                'deposits' => $request->initial_deposit,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Card #{$card->account_number} completed successfully.",
            'data'    => $card->fresh(['ccBranch', 'ccAgent', 'broker', 'marketer', 'branch']),
        ]);
    }

    // ── GET pending CC cards for a branch ──────────────────
    // GET /api/cc/pending
    public function pending(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::with(['ccBranch', 'ccAgent', 'branch'])
                               ->whereNotNull('cc_branch_id')
                               ->where('cc_status', 'accepted'); // sent but not yet completed

        // Branch manager sees only their pending cards
        if ($user->isBranchManager()) {
            $query->where('branch_id', $user->branch_id);
        }

        $cards = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'count'   => $cards->count(),
            'data'    => $cards,
        ]);
    }

    // ── GET CC cards sent by CC branch ─────────────────────
    // GET /api/cc/sent
    public function sent(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::with(['ccAgent', 'branch', 'broker', 'marketer'])
                               ->whereNotNull('cc_branch_id');

        if ($user->isBranchManager()) {
            // CC branch manager sees cards they sent
            $query->where('cc_branch_id', $user->branch_id);
        }

        if ($s = $request->cc_status) $query->where('cc_status', $s);

        $cards = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json(['success' => true, 'data' => $cards]);
    }

    // ── GET notifications ──────────────────────────────────
    // GET /api/cc/notifications
    public function notifications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CcNotification::with(['card', 'fromBranch', 'sentBy'])
                               ->latest();

        if ($user->isBranchManager()) {
            $query->forBranch($user->branch_id);
        }

        $all    = $query->get();
        $unread = $all->where('status', 'unread')->count();

        return response()->json([
            'success'       => true,
            'unread_count'  => $unread,
            'data'          => $all->take(50),
        ]);
    }

    // ── Mark notification as read ──────────────────────────
    // PUT /api/cc/notifications/{id}/read
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notif = CcNotification::findOrFail($id);
        $user  = $request->user();

        if ($user->isBranchManager() && $notif->to_branch_id !== $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $notif->markRead();
        return response()->json(['success' => true]);
    }

    // ── Commission limit validator ─────────────────────────
    private function checkCommissionLimit(
        float $total,
        bool $limitEnabled,
        float $limitAmount,
        Request $request
    ): ?JsonResponse {

        if (!$limitEnabled || $total <= $limitAmount) {
            return null; // No issue
        }

        // Get warning counter from request (client tracks this)
        $warningCount = (int) $request->header('X-Commission-Warning-Count', 0);

        $maxWarnings = Setting::commissionWarningCount();

        if ($warningCount >= $maxWarnings) {
            // Hard block — contact FA
            return response()->json([
                'success'        => false,
                'blocked'        => true,
                'total'          => $total,
                'limit'          => $limitAmount,
                'message'        => "تجاوزت العمولات الحد المسموح ({$limitAmount}$/lot). يرجى التواصل مع المدير المالي لمراجعة هذا الحساب.",
                'contact_fa'     => true,
            ], 422);
        }

        // Warning (not yet blocked)
        $remaining = $maxWarnings - $warningCount;
        $levels    = ['خفيف', 'متوسط', 'قوي'];
        $level     = $levels[min($warningCount, 2)];

        return response()->json([
            'success'           => false,
            'warning'           => true,
            'warning_level'     => $level,
            'warning_number'    => $warningCount + 1,
            'warnings_left'     => $remaining - 1,
            'total'             => $total,
            'limit'             => $limitAmount,
            'message'           => "تحذير {$level}: إجمالي العمولات ({$total}$/lot) يتجاوز الحد ({$limitAmount}$/lot). هل تريد المتابعة؟",
            'can_override'      => true,
        ], 422);
    }
}
