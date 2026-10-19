<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CcNotification, ActivityLog, Employee, ManagerNotification, User};
use App\Http\Controllers\Api\CommissionCardController as CardCtrl;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator, Hash};

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
    /** Commission caps for a CC card ($/lot):
     *   - CC team  (agent + manager)                       ≤ $2
     *   - Branch   (broker + marketer + ext1 + ext2 + ref) ≤ $5
     *   - TOTAL    (CC team + branch)                       ≤ $7
     */
    const CC_TEAM_LIMIT   = 2.0;   // call-center agent + manager
    const CC_BRANCH_LIMIT = 5.0;   // all branch-side commissions
    const CC_TOTAL_LIMIT  = 7.0;   // everything combined
    /** @deprecated kept for back-compat */
    const CC_COMM_LIMIT   = 5.0;

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

    /** Human label for a lead — phone is the primary identity. */
    private function phoneLabel(CommissionCard $card): string
    {
        $cc   = trim((string) $card->client_country_code);
        $ph   = trim((string) $card->client_phone);
        $full = $ph === '' ? '' : trim("{$cc} {$ph}");
        if ($full !== '') return "📱 {$full}";
        return $card->account_number ? "#{$card->account_number}" : '—';
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

    /** Only Call-Center branch staff (or Finance Admin) may create/send CC cards. */
    private function assertCcBranch(Request $request): ?JsonResponse
    {
        if (!$request->user()->isCallCenterStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الصلاحية مقصورة على فرع مركز الاتصال.',
            ], 403);
        }
        return null;
    }

    // ── POST /api/cc/cards — CC creates card (draft) ──────────
    public function store(Request $request): JsonResponse
    {
        if ($err = $this->assertCcBranch($request)) return $err;
        $user = $request->user();

        // Resolve the SOURCE Call-Center branch. CC staff have it as their own branch_id; a Finance
        // Admin (no branch) must rely on the chosen cc_branch_id, else we fall back to the single
        // Call-Center branch. Without this, finance-admin-created cards had cc_branch_id = NULL and
        // sending crashed (from_branch_id cannot be null) — so the card stayed an un-sendable draft.
        $ccBranchId = $user->branch_id
            ?: ($request->cc_branch_id
                ?: \App\Models\Branch::where('is_call_center', true)->whereNull('deleted_at')->value('id'));
        if (!$ccBranchId) {
            return response()->json(['success' => false, 'message' => 'لا يوجد فرع مركز اتصال مُعرّف. فعّل خيار «مركز اتصال» لأحد الفروع أولاً.'], 422);
        }

        // Target branch must be a DIFFERENT (real) branch — not the CC branch itself
        if ((int)$request->target_branch_id === (int)$ccBranchId) {
            return response()->json(['success'=>false,'message'=>'اختر فرعاً وجهةً غير فرع مركز الاتصال.'], 422);
        }

        $v = Validator::make($request->all(), [
            'account_number'    => ['nullable', 'regex:/^\d{6}$/'],
            'month'             => 'required|string|max:20',
            'month_date'        => 'required|date',
            'target_branch_id'  => 'required|exists:branches,id',
            'cc_agent_id'       => 'required|exists:employees,id',
            'account_type_id'   => 'nullable|exists:account_types,id',
            'account_status_id' => 'nullable|exists:account_statuses,id',
            'trading_type_id'   => 'nullable|exists:trading_types,id',
            'account_kind'      => 'nullable|in:new,sub',
            'parent_account'    => ['nullable', 'regex:/^\d{6}$/', 'required_if:account_kind,sub'],
            'notes'             => 'nullable|string|max:2000',
            // Phone is now the PRIMARY required field — the lead is identified by it.
            'client_phone'        => ['required', 'regex:/^\d{6,15}$/'],
            'client_country_code' => 'nullable|string|max:8',
            'client_source'       => 'nullable|string|max:120',
            'cc_agent_commission'   => 'nullable|numeric|min:0',
            'cc_manager_commission' => 'nullable|numeric|min:0',
            'initial_deposit'   => 'nullable|numeric|min:0',
        ], [
            'parent_account.required_if' => 'يجب كتابة رقم الحساب الأساسي للحساب الفرعي.',
            'parent_account.regex'       => 'رقم الحساب الأساسي يجب أن يتكوّن من 6 أرقام بالضبط.',
            'account_number.regex'       => 'رقم الحساب يجب أن يتكوّن من 6 أرقام بالضبط.',
            'client_phone.required'      => 'رقم هاتف العميل إلزامي — هو أساس بناء الكرت.',
            'client_phone.regex'         => 'رقم الهاتف يجب أن يكون أرقامًا فقط (6–15 رقمًا) بدون كود الدولة.',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $accNo = trim((string) $request->account_number);
        $phone = trim((string) $request->client_phone);

        // Verify agent belongs to the CC (source) branch
        $agent = Employee::findOrFail($request->cc_agent_id);
        if ($user->isScopedToBranch() && (int)$agent->branch_id !== (int)$ccBranchId) {
            return response()->json(['success' => false, 'message' => 'يجب أن ينتمي الموظف لفرعك.'], 403);
        }

        // ── ⚠️ CC TEAM CAP: agent + manager commission ≤ $2 ──
        $ccAgentComm = $request->filled('cc_agent_commission')
            ? (float) $request->cc_agent_commission : (float) ($agent->cc_commission ?? 1.00);
        $ccMgrComm = (float) ($request->cc_manager_commission ?? 0);
        if ($ccAgentComm + $ccMgrComm > self::CC_TEAM_LIMIT + 0.0001) {
            return response()->json([
                'success' => false,
                'cc_limit_exceeded' => true,
                'message' => sprintf('⛔ عمولة موظف مركز الاتصال (%.2f$) + المديرة (%.2f$) = %.2f$ تتجاوز الحد المسموح لمركز الاتصال (%.0f$).',
                    $ccAgentComm, $ccMgrComm, $ccAgentComm + $ccMgrComm, self::CC_TEAM_LIMIT),
            ], 422);
        }

        // ── No duplicate phone in the follow-up list ──
        // If this customer's phone is already an ACTIVE lead, DON'T create a duplicate — instead
        // nudge the handling branch to re-call and tell the CC side it's already under follow-up.
        $dupLead = $this->activeDuplicatePhone($phone, $request->client_country_code);
        if ($dupLead) {
            $this->notifyDuplicateLead($dupLead, $user);
            return response()->json([
                'success'   => false,
                'duplicate' => true,
                'message'   => "📌 العميل (📱 {$this->phoneLabel($dupLead)}) تحت المتابعة بالفعل لدى فرع «{$dupLead->branch?->name_ar}». تم تنبيه الفرع لمعاودة الاتصال — لم يُنشأ كرت مكرّر.",
            ], 409);
        }

        // Check duplicate account number (only when an account number is supplied)
        if ($accNo !== '' && CommissionCard::where('account_number', $accNo)
                          ->where('month', $request->month)
                          ->whereNull('deleted_at')->exists()) {
            return response()->json([
                'success' => false,
                'message' => "الحساب #{$accNo} موجود مسبقاً لشهر {$request->month}.",
            ], 409);
        }

        $card = DB::transaction(function () use ($request, $agent, $user, $ccBranchId, $accNo) {
            $card = CommissionCard::create([
                'account_number'      => $accNo !== '' ? $accNo : null,
                'month'               => $request->month,
                'month_date'          => $request->month_date,
                'branch_id'           => $request->target_branch_id,
                'cc_branch_id'        => $ccBranchId,
                'cc_agent_id'         => $agent->id,
                'cc_agent_commission' => $request->filled('cc_agent_commission')
                                            ? (float) $request->cc_agent_commission
                                            : ($agent->cc_commission ?? 1.00),
                'cc_manager_commission' => $request->cc_manager_commission ?? 0,
                'initial_deposit'     => $request->initial_deposit ?? 0,
                'account_type_id'     => $request->account_type_id,
                'account_status_id'   => $request->account_status_id,
                'trading_type_id'     => $request->trading_type_id,
                'account_kind'        => $request->account_kind ?? 'new',
                'parent_account'      => ($request->account_kind === 'sub')
                                            ? preg_replace('/[^0-9]/', '', (string) $request->parent_account) ?: null
                                            : null,
                'notes'               => $request->notes,
                'client_phone'        => $request->client_phone,
                'client_country_code' => $request->client_country_code ?: '+965',
                'client_source'       => $request->client_source,
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

        CardCtrl::bumpCardsVersion();
        $label = $card->account_number ? "#{$card->account_number}" : "📱 {$phone}";
        return response()->json([
            'success' => true,
            'message' => "تم إنشاء الكرت {$label}. اضغط «إرسال» لإبلاغ الفرع.",
            'data'    => $card->load(['branch']),
        ], 201);
    }

    // ── PUT /api/cc/cards/{id} — edit a DRAFT card before sending ──
    public function update(Request $request, int $id): JsonResponse
    {
        if ($err = $this->assertCcBranch($request)) return $err;
        $card = CommissionCard::findOrFail($id);
        $this->assertCcOwnership($card, $request);

        if ($card->cc_status !== 'cc_pending') {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن تعديل الكرت بعد إرساله (الحالة: {$card->cc_status}).",
            ], 422);
        }

        $v = Validator::make($request->all(), [
            'account_number'    => ['sometimes', 'nullable', 'regex:/^\d{6}$/'],
            'month'             => 'sometimes|required|string|max:20',
            'month_date'        => 'sometimes|required|date',
            'target_branch_id'  => 'sometimes|required|exists:branches,id',
            'cc_agent_id'       => 'sometimes|required|exists:employees,id',
            'account_type_id'   => 'nullable|exists:account_types,id',
            'account_status_id' => 'nullable|exists:account_statuses,id',
            'trading_type_id'   => 'nullable|exists:trading_types,id',
            'account_kind'      => 'nullable|in:new,sub',
            'parent_account'    => ['nullable', 'regex:/^\d{6}$/', 'required_if:account_kind,sub'],
            'notes'             => 'nullable|string|max:2000',
            'client_phone'        => ['sometimes', 'required', 'regex:/^\d{6,15}$/'],
            'client_country_code' => 'nullable|string|max:8',
            'client_source'       => 'nullable|string|max:120',
            'cc_agent_commission'   => 'nullable|numeric|min:0',
            'cc_manager_commission' => 'nullable|numeric|min:0',
            'initial_deposit'   => 'nullable|numeric|min:0',
        ], [
            'parent_account.required_if' => 'يجب كتابة رقم الحساب الأساسي للحساب الفرعي.',
            'parent_account.regex'       => 'رقم الحساب الأساسي يجب أن يتكوّن من 6 أرقام بالضبط.',
            'account_number.regex'       => 'رقم الحساب يجب أن يتكوّن من 6 أرقام بالضبط.',
            'client_phone.regex'         => 'رقم الهاتف يجب أن يكون أرقامًا فقط (6–15 رقمًا) بدون كود الدولة.',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $fields = $request->only([
            'account_number','month','month_date','account_type_id','account_status_id',
            'trading_type_id','account_kind','notes','client_phone','client_country_code','client_source',
            'cc_agent_commission','cc_manager_commission','initial_deposit',
        ]);
        $fields['parent_account'] = ($request->account_kind === 'sub')
            ? (preg_replace('/[^0-9]/', '', (string) $request->parent_account) ?: null) : null;
        if ($request->filled('target_branch_id')) $fields['branch_id']   = (int)$request->target_branch_id;
        if ($request->filled('cc_agent_id'))      $fields['cc_agent_id'] = (int)$request->cc_agent_id;

        $card->update($fields);
        ActivityLog::record('cc_card_edited', $card, ['by' => $request->user()->id]);
        CardCtrl::bumpCardsVersion();

        return response()->json(['success' => true, 'message' => "تم تعديل الكرت #{$card->account_number}.", 'data' => $card->load(['branch'])]);
    }

    // ── POST /api/cc/cards/{id}/delete-secure — delete after password re-auth ──
    public function deleteSecure(Request $request, int $id): JsonResponse
    {
        if ($err = $this->assertCcBranch($request)) return $err;
        $user = $request->user();
        $card = CommissionCard::findOrFail($id);
        $this->assertCcOwnership($card, $request);

        if (!$request->filled('password') || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'كلمة المرور غير صحيحة.'], 403);
        }

        $branchId = $card->branch_id;
        $acc      = $card->account_number;
        $card->update(['status' => 'inactive']);
        $card->delete();
        ActivityLog::record('cc_card_deleted', $card, ['by' => $user->id, 'account' => $acc]);
        CardCtrl::bumpCardsVersion();

        return response()->json(['success' => true, 'message' => "تم حذف الكرت #{$acc}."]);
    }

    // ── POST /api/cc/cards/{id}/send — CC sends to branch ──────
    public function send(Request $request, int $id): JsonResponse
    {
        if ($err = $this->assertCcBranch($request)) return $err;
        $card = CommissionCard::findOrFail($id);
        $this->assertCcOwnership($card, $request);

        if ($card->cc_status !== 'cc_pending') {
            return response()->json([
                'success' => false,
                'message' => "حالة الكرت '{$card->cc_status}' — لا يمكن إرساله مجدداً.",
            ], 422);
        }

        // No duplicate phone in the follow-up list — if this customer is already an active lead,
        // nudge the handling branch to re-call instead of sending a duplicate row.
        $dupLead = $this->activeDuplicatePhone((string) $card->client_phone, $card->client_country_code, $card->id);
        if ($dupLead) {
            $this->notifyDuplicateLead($dupLead, $request->user());
            return response()->json([
                'success'   => false,
                'duplicate' => true,
                'message'   => "📌 العميل (📱 {$this->phoneLabel($dupLead)}) تحت المتابعة بالفعل. تم تنبيه الفرع لمعاودة الاتصال — لم يُرسَل كرت مكرّر. يمكنك حذف هذه المسودة.",
            ], 409);
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
                'message'        => "📩 عميل مهتم من مركز الاتصال — هاتف: {$this->phoneLabel($card)} ({$card->month}). اتصلوا بالعميل وحاولوا فتح الحساب ثم حدّثوا الحالة.",
            ]);

            ActivityLog::record('cc_card_sent', $card, ['to_branch' => $card->branch_id]);
        });

        // Per-manager notifications (accurate tracker) — fan out to the target branch's
        // managers (+ Finance Admins so they see every incoming CC card).
        $this->notifyManagers($card, $request->user());

        return response()->json([
            'success' => true,
            'message' => "✅ تم إرسال الكرت #{$card->account_number} للفرع — بانتظار الرد.",
        ]);
    }

    /** Create one notification row per recipient manager for a new CC card. */
    private function notifyManagers(CommissionCard $card, User $actor): void
    {
        try {
            // Target branch manager (but never notify the actor about their own action) PLUS
            // every Finance Admin — FAs oversee the whole pipeline and must see ALL CC activity,
            // even cards they created/sent themselves.
            $recipients = User::where('is_active', true)
                ->where(function ($q) use ($card, $actor) {
                    $q->where(function ($w) use ($card, $actor) {
                        $w->where('role', 'branch_manager')
                          ->where('branch_id', $card->branch_id)
                          ->where('id', '!=', $actor->id);
                    })->orWhere('role', 'finance_admin');
                })
                ->get(['id']);

            if ($recipients->isEmpty()) return;

            $phoneFull = trim(trim((string) $card->client_country_code) . ' ' . trim((string) $card->client_phone));
            ManagerNotification::fanOut($recipients, [
                'type'           => 'cc_new_card',
                'card_id'        => $card->id,
                'account_number' => $card->account_number,
                'month'          => $card->month,
                'from_user_id'   => $actor->id,
                'from_branch_id' => $card->cc_branch_id,
                'to_branch_id'   => $card->branch_id,
                'title'          => "عميل مهتم من مركز الاتصال: 📱 {$phoneFull}",
                'body'           => "وصل عميل مهتم (هاتف: {$phoneFull} — {$card->month}) من مركز الاتصال. اتصلوا به وحاولوا فتح الحساب، ثم حدّثوا الحالة.",
                'data'           => ['client_phone' => $phoneFull, 'account_number' => $card->account_number, 'month' => $card->month],
            ]);
        } catch (\Throwable $e) {
            \Log::warning('notifyManagers failed: '.$e->getMessage());
        }
    }

    /** An ACTIVE lead (still under follow-up) with the same phone, if any. */
    private function activeDuplicatePhone(string $phone, ?string $code, ?int $excludeId = null): ?CommissionCard
    {
        $phone = trim($phone);
        if ($phone === '') return null;
        return CommissionCard::whereNotNull('cc_branch_id')
            ->whereIn('cc_status', ['branch_pending', 'accepted', 'in_progress'])
            ->where('client_phone', $phone)
            ->where('client_country_code', $code ?: '+965')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderByDesc('id')->first();
    }

    /** Same customer sent again while still under follow-up → no duplicate row; instead
     *  nudge the handling branch to re-call, and tell the CC side it's already in progress. */
    private function notifyDuplicateLead(CommissionCard $existing, User $actor): void
    {
        try {
            \App\Models\CcFollowup::create([
                'card_id'   => $existing->id,
                'user_id'   => $actor->id,
                'branch_id' => $existing->branch_id,
                'outcome'   => 'in_progress',
                'note'      => '🔁 مركز الاتصال أرسل العميل مجددًا — يرجى معاودة الاتصال به.',
            ]);
            $existing->touch();
            $phoneFull = trim(trim((string) $existing->client_country_code) . ' ' . trim((string) $existing->client_phone));

            // 1) Target branch manager → RE-CONTACT the customer.
            $branchMgrs = User::where('is_active', true)->where('role', 'branch_manager')
                ->where('branch_id', $existing->branch_id)->get(['id']);
            if ($branchMgrs->isNotEmpty()) {
                ManagerNotification::fanOut($branchMgrs, [
                    'type' => 'cc_new_card', 'card_id' => $existing->id, 'month' => $existing->month,
                    'from_user_id' => $actor->id, 'from_branch_id' => $existing->cc_branch_id, 'to_branch_id' => $existing->branch_id,
                    'title' => "🔁 معاودة الاتصال بالعميل: 📱 {$phoneFull}",
                    'body'  => "العميل ({$phoneFull}) لا يزال تحت المتابعة — يرجى معاودة الاتصال به. (لم يُنشأ كرت جديد لأنه موجود بالفعل.)",
                    'data'  => ['client_phone' => $phoneFull],
                ]);
            }
            // 2) CC branch manager(s) + Finance Admin → already under follow-up.
            $ccMgrs = User::where('is_active', true)->where(function ($q) use ($existing) {
                $q->where(function ($w) use ($existing) { $w->where('role', 'branch_manager')->where('branch_id', $existing->cc_branch_id); })
                  ->orWhere('role', 'finance_admin');
            })->get(['id']);
            if ($ccMgrs->isNotEmpty()) {
                ManagerNotification::fanOut($ccMgrs, [
                    'type' => 'cc_new_card', 'card_id' => $existing->id, 'month' => $existing->month,
                    'from_user_id' => $actor->id, 'from_branch_id' => $existing->cc_branch_id, 'to_branch_id' => $existing->branch_id,
                    'title' => "📌 عميل تحت المتابعة بالفعل: 📱 {$phoneFull}",
                    'body'  => "العميل ({$phoneFull}) موجود بالفعل تحت المتابعة — لم يُنشأ كرت مكرّر، وتم تنبيه الفرع لمعاودة الاتصال.",
                    'data'  => ['client_phone' => $phoneFull],
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('notifyDuplicateLead failed: '.$e->getMessage());
        }
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

        // The branch may complete (open the account) directly from any active lead state —
        // the separate "accept" gate was merged into the follow-up flow.
        if (!in_array($card->cc_status, ['branch_pending', 'accepted', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن فتح حساب لكرت بحالة '{$card->cc_status}'.",
            ], 422);
        }

        $v = Validator::make($request->all(), [
            // Opening the account REQUIRES a 6-digit account number — this is the proof
            // the lead converted into a real account.
            'account_number'      => ['required', 'regex:/^\d{6}$/'],
            'broker_id'           => 'nullable|exists:employees,id',   // null = Self (the branch)
            'broker_commission'   => 'nullable|numeric|min:0',
            'marketer_id'         => 'nullable|exists:employees,id',
            'marketer_commission' => 'nullable|numeric|min:0',
            'ext_marketer1_id'    => 'nullable|exists:employees,id',
            'ext_commission1'     => 'nullable|numeric|min:0',
            'ext_marketer2_id'    => 'nullable|exists:employees,id',
            'ext_commission2'     => 'nullable|numeric|min:0',
            'referral_account'    => ['nullable', 'regex:/^\d{6}$/'],
            'referral_commission' => 'nullable|numeric|min:0',
            'initial_deposit'     => 'required|numeric|min:0',
            'monthly_deposit'     => 'nullable|numeric|min:0',
            'forex_commission'    => 'nullable|numeric|min:0',
            'futures_commission'  => 'nullable|numeric|min:0',
        ], [
            'account_number.required' => 'رقم الحساب إلزامي عند فتح الحساب.',
            'account_number.regex'    => 'رقم الحساب يجب أن يتكوّن من 6 أرقام بالضبط.',
            'referral_account.regex'  => 'رقم حساب المُحيل يجب أن يتكوّن من 6 أرقام بالضبط.',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // No duplicate account for the same month.
        $accNo = trim((string) $request->account_number);
        $dup = CommissionCard::where('account_number', $accNo)
            ->where('month', $card->month)
            ->where('id', '!=', $card->id)
            ->whereNull('deleted_at')->exists();
        if ($dup) {
            return response()->json([
                'success' => false,
                'message' => "الحساب #{$accNo} موجود مسبقاً لشهر {$card->month}.",
            ], 409);
        }

        // ── ⚠️ BRANCH CAP: all branch-side commissions ≤ $5 ──────
        // (broker + marketer + external marketers + referral). Self (null) → 0 commission.
        $brokerComm   = $request->broker_id   ? (float) $request->broker_commission   : 0.0;
        $marketerComm = $request->marketer_id ? (float) ($request->marketer_commission ?? 0) : 0.0;
        $branchTotal  = $brokerComm + $marketerComm
            + (float) ($request->ext_commission1 ?? 0) + (float) ($request->ext_commission2 ?? 0)
            + (float) ($request->referral_commission ?? 0);
        if ($branchTotal > self::CC_BRANCH_LIMIT + 0.0001) {
            return response()->json([
                'success'           => false,
                'cc_limit_exceeded' => true,
                'total'             => round($branchTotal, 2),
                'limit'             => self::CC_BRANCH_LIMIT,
                'message'           => sprintf(
                    '⛔ إجمالي عمولات الفرع (%.2f$) — البروكر + المسوّقون + الإحالة — يتجاوز الحد المسموح (%.0f$) لكرت مركز الاتصال.',
                    $branchTotal, self::CC_BRANCH_LIMIT
                ),
            ], 422);
        }

        // ── ⚠️ TOTAL CEILING: branch + CC team ≤ $7 ──
        $total7 = $branchTotal
            + (float) ($card->cc_agent_commission ?? 0) + (float) ($card->cc_manager_commission ?? 0);
        if ($total7 > self::CC_TOTAL_LIMIT + 0.0001) {
            return response()->json([
                'success'           => false,
                'cc_limit_exceeded' => true,
                'total'             => round($total7, 2),
                'limit'             => self::CC_TOTAL_LIMIT,
                'message'           => sprintf(
                    '⛔ إجمالي عمولات هذا الحساب (%.2f$) يتجاوز السقف المسموح (%.0f$) — شاملًا الفرع وموظف ومديرة مركز الاتصال. يرجى تعديل العمولات.',
                    $total7, self::CC_TOTAL_LIMIT
                ),
            ], 422);
        }

        DB::transaction(function () use ($request, $card, $accNo, $brokerComm, $marketerComm) {
            $card->update([
                'account_number'      => $accNo,
                'broker_id'           => $request->broker_id ?: null,      // null = Self (the branch)
                'broker_commission'   => $brokerComm,                       // 0 when Self
                'marketer_id'         => $request->marketer_id ?: null,
                'marketer_commission' => $marketerComm,                     // 0 when Self / none
                'ext_marketer1_id'    => $request->ext_marketer1_id,
                'ext_commission1'     => $request->ext_commission1 ?? 0,
                'ext_marketer2_id'    => $request->ext_marketer2_id,
                'ext_commission2'     => $request->ext_commission2 ?? 0,
                'referral_account'    => $request->referral_account ?: null,
                'referral_commission' => $request->referral_commission ?? 0,
                'initial_deposit'     => $request->initial_deposit,
                'monthly_deposit'     => $request->monthly_deposit ?? 0,
                'forex_commission'    => $request->forex_commission ?? 0,
                'futures_commission'  => $request->futures_commission ?? 0,
                'cc_status'           => 'completed',
                'status'              => 'new_added',
            ]);

            \App\Models\CcFollowup::create([
                'card_id'   => $card->id,
                'user_id'   => $request->user()->id,
                'branch_id' => $card->branch_id,
                'outcome'   => 'opened',
                'note'      => "تم فتح الحساب #{$accNo}.",
            ]);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $request->user()->id,
                'responded_by'   => $request->user()->id,
                'type'           => 'card_completed',
                'status'         => 'unread',
                'message'        => "🏁 العميل (📱 {$this->phoneLabel($card)}) فتح الحساب #{$accNo} ({$card->month}).",
            ]);

            ActivityLog::record('cc_card_completed', $card);
        });

        CardCtrl::bumpCardsVersion();
        return response()->json([
            'success' => true,
            'message' => "🏁 تم إكمال الكرت #{$card->account_number} بنجاح.",
            'data'    => $card->fresh(),
        ]);
    }

    // ── PUT /api/cc/cards/{id}/followup — Branch logs an update (still in progress) ──
    // Customer was contacted but hasn't decided yet → lead stays in the follow-up list.
    public function followup(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertBranchOwnership($card, $request);

        if (!in_array($card->cc_status, ['branch_pending', 'accepted', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن تحديث كرت بحالة '{$card->cc_status}'.",
            ], 422);
        }

        $v = Validator::make($request->all(), [
            'note' => 'required|string|min:2|max:1000',
        ], [
            'note.required' => 'اكتب ملخص ما تمّ مع العميل.',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        DB::transaction(function () use ($card, $request) {
            $card->update(['cc_status' => 'in_progress']);

            \App\Models\CcFollowup::create([
                'card_id'   => $card->id,
                'user_id'   => $request->user()->id,
                'branch_id' => $card->branch_id,
                'outcome'   => 'in_progress',
                'note'      => $request->note,
            ]);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $request->user()->id,
                'responded_by'   => $request->user()->id,
                'type'           => 'card_followup',
                'status'         => 'unread',
                'message'        => "🔄 تحديث متابعة العميل (📱 {$this->phoneLabel($card)}): {$request->note}",
            ]);

            ActivityLog::record('cc_card_followup', $card, ['note' => $request->note]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل التحديث وإشعار مركز الاتصال. يبقى العميل في قائمة المتابعة.',
        ]);
    }

    // ── PUT /api/cc/cards/{id}/decline — Customer refused (terminal) ──
    // Customer asked not to be contacted again → lead LEAVES the follow-up list.
    public function decline(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertBranchOwnership($card, $request);

        if (!in_array($card->cc_status, ['branch_pending', 'accepted', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن إغلاق كرت بحالة '{$card->cc_status}'.",
            ], 422);
        }

        $v = Validator::make($request->all(), [
            'reason' => 'required|string|min:2|max:1000',
        ], [
            'reason.required' => 'اكتب سبب عدم رغبة العميل.',
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        DB::transaction(function () use ($card, $request) {
            $card->update([
                'cc_status'           => 'declined',
                'cc_rejection_reason' => $request->reason,
            ]);

            \App\Models\CcFollowup::create([
                'card_id'   => $card->id,
                'user_id'   => $request->user()->id,
                'branch_id' => $card->branch_id,
                'outcome'   => 'declined',
                'note'      => $request->reason,
            ]);

            CcNotification::create([
                'card_id'        => $card->id,
                'from_branch_id' => $card->branch_id,
                'to_branch_id'   => $card->cc_branch_id,
                'sent_by'        => $request->user()->id,
                'responded_by'   => $request->user()->id,
                'type'           => 'card_declined',
                'status'         => 'unread',
                'message'        => "🚫 العميل (📱 {$this->phoneLabel($card)}) رفض ولا يريد اتصالًا مجددًا — السبب: {$request->reason}",
            ]);

            ActivityLog::record('cc_card_declined', $card, ['reason' => $request->reason]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إغلاق العميل وإزالته من قائمة المتابعة وإشعار مركز الاتصال.',
        ]);
    }

    // ── GET /api/cc/leads — shared "العملاء قيد المتابعة" list ──
    // Visible to the target branch (its leads only), the CC branch (its sent leads),
    // and Finance Admin (all). A lead stays here until it is opened or declined.
    public function leads(Request $request): JsonResponse
    {
        $user = $request->user();
        $with = ['ccBranch', 'branch', 'ccAgent', 'ccFollowups.user'];

        // Scope: branch users see leads to/from their branch; Finance Admin sees all.
        $scope = function ($q) use ($user) {
            $q->whereNotNull('cc_branch_id');
            if ($user->isScopedToBranch()) {
                $bid = $user->branch_id;
                $q->where(fn($w) => $w->where('branch_id', $bid)->orWhere('cc_branch_id', $bid));
            }
            return $q;
        };

        $phoneKey = "CONCAT(COALESCE(client_country_code,''),'|',COALESCE(client_phone,''))";

        // ── Classification counts (all CC leads in scope). Active is counted by DISTINCT phone
        //    so the same customer is never counted twice. ──
        $stats = [
            'in_followup' => (int) (clone $scope(CommissionCard::query()))
                                ->whereIn('cc_status', ['branch_pending', 'accepted', 'in_progress'])
                                ->distinct()->count(DB::raw($phoneKey)),
            'opened'      => (clone $scope(CommissionCard::query()))->where('cc_status', 'completed')->count(),
            'declined'    => (clone $scope(CommissionCard::query()))->where('cc_status', 'declined')->count(),
        ];

        // ── The list for the chosen view (tab) ──
        $view    = $request->query('view', 'active');   // active | opened | declined
        $statuses = $view === 'opened'   ? ['completed']
                  : ($view === 'declined' ? ['declined']
                  : ['branch_pending', 'accepted', 'in_progress']);

        $query = $scope(CommissionCard::with($with))->whereIn('cc_status', $statuses);

        // Active view: ONE row per phone (latest card) — never show the same customer twice.
        if ($view === 'active') {
            $sub = $scope(CommissionCard::query())
                ->whereIn('cc_status', ['branch_pending', 'accepted', 'in_progress'])
                ->selectRaw("MAX(id) id")->groupByRaw($phoneKey);
            $query->whereIn('id', $sub->pluck('id'));
        }

        $cards = $query->orderByRaw("FIELD(cc_status,'branch_pending','in_progress','accepted')")
            ->orderBy('updated_at', 'desc')
            ->limit(1000)->get();

        // CC agent/manager commissions are private to the Call Center — hide them from the
        // TARGET-branch viewer (but keep them visible to the CC branch and Finance Admin).
        if ($user->isScopedToBranch()) {
            foreach ($cards as $c) {
                if ((int) $c->branch_id === (int) $user->branch_id && (int) $c->cc_branch_id !== (int) $user->branch_id) {
                    $c->makeHidden(['cc_agent_commission', 'cc_manager_commission']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'view'    => $view,
            'stats'   => $stats,
            'count'   => $cards->count(),
            'data'    => $cards,
        ]);
    }

    // ── GET /api/cc/cards/{id}/followups — a lead's call log ──
    public function followups(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $user = $request->user();
        if ($user->isScopedToBranch()
            && (int) $card->branch_id !== (int) $user->branch_id
            && (int) $card->cc_branch_id !== (int) $user->branch_id) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية.'], 403);
        }

        $log = $card->ccFollowups()->with('user:id,name')->get();
        return response()->json(['success' => true, 'data' => $log]);
    }

    // ── POST /api/cc/cards/{id}/resend — CC resends rejected card ─
    public function resend(Request $request, int $id): JsonResponse
    {
        if ($err = $this->assertCcBranch($request)) return $err;
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

        // Re-notify the target branch's managers (+ Finance Admins), same as a fresh send.
        $this->notifyManagers($card, $request->user());

        return response()->json([
            'success' => true,
            'message' => "✅ تمت إعادة إرسال الكرت #{$card->account_number} للفرع.",
        ]);
    }

    // ── GET /api/cc/pending — Branch: cards awaiting action ────
    public function pending(Request $request): JsonResponse
    {
        $user  = $request->user();
        $with  = ['ccBranch', 'ccAgent', 'accountType', 'accountStatus'];

        $base = CommissionCard::with($with)->whereNotNull('cc_branch_id');
        if ($user->isScopedToBranch()) {
            $base->where('branch_id', $user->branch_id);
        }

        // Leads awaiting the branch's action (sent, being followed up, or accepted-legacy)
        $cards = (clone $base)->whereIn('cc_status', ['branch_pending', 'accepted', 'in_progress'])
                              ->with('ccFollowups.user')
                              ->orderBy('updated_at', 'desc')->get();

        // Completed CC cards already with the branch (e.g. imported / historical) — for visibility
        $completed = (clone $base)->where('cc_status', 'completed')
                                  ->orderBy('month_date', 'desc')->limit(500)->get();

        // The CC agent's and CC manager's commissions are PRIVATE to the Call Center —
        // the target branch must never see them. Finance Admin (not branch-scoped) sees all.
        if ($user->isScopedToBranch()) {
            $hide = ['cc_agent_commission', 'cc_manager_commission'];
            $cards->each->makeHidden($hide);
            $completed->each->makeHidden($hide);
        }

        return response()->json([
            'success'        => true,
            'count'          => $cards->count(),
            'data'           => $cards,
            'completed'      => $completed,
            'completedCount' => $completed->count(),
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

        // Honor per_page (the dashboard/reports load every card to compute KPIs client-side).
        $per   = min(max((int) $request->per_page ?: 50, 1), 2000);
        $cards = $query->orderBy('created_at', 'desc')->paginate($per);

        // مديرة مركز الاتصال = مدير فرع مركز الاتصال (مشتق من cc_branch_id)
        $ccBranchIds = collect($cards->items())->pluck('cc_branch_id')->unique()->filter()->values();
        if ($ccBranchIds->isNotEmpty()) {
            $managers = \App\Models\User::whereIn('branch_id', $ccBranchIds)
                ->whereIn('role', ['branch_manager', 'finance_admin'])
                ->orderByRaw("FIELD(role,'branch_manager','finance_admin')")
                ->get(['name', 'branch_id'])
                ->groupBy('branch_id')
                ->map(fn($g) => $g->first()->name);
            foreach ($cards as $c) {
                $c->cc_manager_name = $managers[$c->cc_branch_id] ?? null;
            }
        }

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
