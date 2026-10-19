<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, ImportBatch, Employee, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator};
use Carbon\Carbon;

class ImportController extends Controller
{
    /**
     * POST /api/import
     *
     * Accepts JSON array of rows from the frontend (after parsing Excel).
     *
     * Row format:
     * {
     *   "ac_no": "719750",
     *   "broker": "Samer Obeid",
     *   "broker_commission": 4,
     *   "marketing": "Fahad Bloshi",
     *   "marketing_commission": 3,
     *   "ext_marketer1": "",
     *   "ext_commission1": 0,
     *   "ext_marketer2": "",
     *   "ext_commission2": 0,
     *   "month": "Jan 2025",
     *   "initial_deposit": 5000,
     *   "monthly_deposit": 12000,
     *   "new_or_sub": "NEW",
     *   "type": "ECN"
     * }
     */
    public function import(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'rows'         => 'required|array|min:1',
            'rows.*.ac_no' => 'required|string',
            'rows.*.month' => 'nullable|string',
            'filename'     => 'nullable|string|max:255',
            'mode'         => 'nullable|in:normal,cc',
            'cc_branch_id' => 'nullable|exists:branches,id',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $batchCode = 'IMP-' . now()->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));

        $batch = ImportBatch::create([
            'batch_code'  => $batchCode,
            'filename'    => $request->filename ?? 'upload-' . now()->format('YmdHis'),
            'total_rows'  => count($request->rows),
            'status'      => 'processing',
            'imported_by' => $request->user()->id,
            'started_at'  => now(),
        ]);

        $imported = 0;
        $skipped  = 0;
        $linked   = 0;   // CC rows auto-linked to an existing branch card
        $failed   = 0;
        $ccTargets = []; // [branch_id => count]  CC cards that landed on a real branch
        $createdEmps = 0; // employees auto-defined from broker/marketer columns

        // ── Smart cell classifiers (shared by row-skip + employee creation) ──
        // A value that is only digits/decimals/thousands/currency (".5", "1,000", "0", account no.)
        $looksNumeric = function ($v) {
            $v = trim((string) $v);
            if ($v === '') return false;
            $t = preg_replace('/[\s,\$%]/', '', $v);
            if ($t === '') return false;
            return (bool) preg_match('/^[-+]?(\d+\.?\d*|\.\d+)$/', $t);
        };
        // Punctuation / symbol only ("-", "—", ".", "$", "/")
        $isPunct = function ($v) {
            $v = trim((string) $v);
            return $v !== '' && (bool) preg_match('/^[\p{P}\p{S}\s]+$/u', $v);
        };
        // Column-title text that leaked into a data row ("Broker commission per lot", "رقم الحساب")
        $HEADER_RE = '/\b(broker|market(ing|er)?|commission|comm|per\s*lot|\blot\b|account|ac[\s_]*no|month|deposit|referral|target|branch|agent|status|serial|total|name)\b/i';
        $AR_HEADER = ['مسوق','مسوّق','بروكر','عموله','عمولة','الحساب','الشهر','الايداع','إيداع','ايداع','المحيل','الفرع','الحاله','الحالة','وكيل','الاجمالي','إجمالي','اجمالي','الاسم','مسلسل','رقم'];
        $isHeaderish = function ($v) use ($HEADER_RE, $AR_HEADER) {
            $v = trim((string) $v);
            if ($v === '') return false;
            if (preg_match($HEADER_RE, $v)) return true;
            foreach ($AR_HEADER as $w) { if (mb_strpos($v, $w) !== false) return true; }
            return false;
        };

        // A per-lot commission is never in the millions. A misaligned cell (an account number
        // landing in a commission column) overflows decimal(8,2) and kills the whole row →
        // treat any absurd commission value as 0 instead of failing the import.
        $commVal = function ($v) {
            $v = (float) preg_replace('/[,\s\$%]/', '', (string) $v);
            return (abs($v) > 999999) ? 0.0 : round($v, 2);
        };

        // Auto-define an employee from a broker/marketer column value.
        // A pure account number = a REFERRAL → never created as an employee.
        $importUserId = $request->user()->id;

        // Case/space-insensitive name key → "m. adnan", "M. Adnan", "M.  Adnan" all match.
        $normName = function ($name) {
            return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $name)));
        };
        $empLc = [];   // normalized name → employee id (built after $empCache loads)

        $ensureEmp = function ($name, $role, $branchId) use (&$empCache, &$empLc, &$createdEmps, $importUserId, $looksNumeric, $isPunct, $isHeaderish, $normName) {
            $name = trim((string) $name);
            if ($name === '')        return null;
            if (preg_match('/^(self|direct|ذاتي)$/iu', $name)) return null;  // "self" = no employee (direct)
            if ($isPunct($name))     return null;   // -, —, ., $, /
            if ($looksNumeric($name)) return null;  // 0, .5, 1.5, 1,000, account numbers (referral)
            if ($isHeaderish($name)) return null;   // "Broker commission per lot" and other column titles
            if (mb_strlen($name) < 2) return null;  // stray single char
            $key = $normName($name);
            if (isset($empLc[$key])) return $empLc[$key];   // same name, any capitalization → one employee
            $emp = \App\Models\Employee::create([
                'name'        => $name,
                'role'        => $role,
                'branch_id'   => $branchId,
                'status'      => 'approved',
                'added_by'    => $importUserId,
                'approved_by' => $importUserId,
                'approved_at' => now(),
                'is_active'   => true,
            ]);
            $empCache[$name] = $emp->id;
            $empLc[$key]     = $emp->id;
            $createdEmps++;
            return $emp->id;
        };
        $errors   = [];

        // Employee name → ID cache
        $empCache = Employee::where('status', 'approved')
                            ->pluck('id', 'name')
                            ->toArray();
        foreach ($empCache as $nm => $id) { $empLc[$normName($nm)] = $id; }  // normalized lookup

        // ── Call Center import mode ──
        $mode       = $request->input('mode', 'normal');
        $ccBranchId = $request->input('cc_branch_id') ? (int) $request->cc_branch_id : null;
        $branchCache = [];                       // branch name (ar/en, lowercased) → id
        if ($mode === 'cc') {
            foreach (\App\Models\Branch::all(['id','name_ar','name_en']) as $b) {
                if ($b->name_ar) $branchCache[mb_strtolower(trim($b->name_ar))] = $b->id;
                if ($b->name_en) $branchCache[mb_strtolower(trim($b->name_en))] = $b->id;
            }
        }

        $prevAcNo = ''; $prevMonth = '';
        DB::transaction(function () use ($request, $batch, &$empCache, &$empLc, $normName, $mode, $ccBranchId, $branchCache, $ensureEmp, $commVal, $looksNumeric, $isHeaderish, &$imported, &$skipped, &$linked, &$failed, &$errors, &$ccTargets, &$createdEmps, &$prevAcNo, &$prevMonth) {
            foreach ($request->rows as $i => $row) {
                $rowNum = $i + 1;

                try {
                    $acRaw     = trim((string)($row['ac_no']     ?? ''));
                    $brokerRaw = trim((string)($row['broker']    ?? ''));
                    $mktRaw    = trim((string)($row['marketing'] ?? ''));

                    // SMART: silently ignore header / sort / label rows that leaked into the data.
                    // A row that carries a real (numeric) account number is ALWAYS data and is
                    // never skipped — even if its broker/marketer NAME happens to contain a word
                    // like "market"/"agent"/"name" (real names tripped $isHeaderish and whole
                    // accounts were being dropped silently). We only skip true label/title rows,
                    // which never have a numeric account: a non-numeric (non-empty) account cell,
                    // or a blank-account row whose broker/marketing cell holds a column title.
                    $acIsNum = $looksNumeric($acRaw);
                    if (!$acIsNum && (
                            $acRaw !== ''                                  // non-numeric account text = a label/title row
                            || $isHeaderish($brokerRaw) || $isHeaderish($mktRaw)  // blank-account separator/total row
                        )) {
                        continue;
                    }

                    $acNo  = trim((string)($row['ac_no']  ?? ''));
                    $month = trim((string)($row['month']  ?? ''));

                    // SMART: a row with NO account number is a continuation of the account above
                    // (same account, extra marketer) → inherit ITS account & ITS month only.
                    // A NEW account with a blank month keeps it blank — never borrow the previous
                    // account's month (that's how unrelated accounts wrongly got "May 2026").
                    $isCont = ($acNo === '');
                    if ($isCont) {
                        if ($prevAcNo  !== '')                   $acNo  = $prevAcNo;
                        if ($month === '' && $prevMonth !== '')  $month = $prevMonth;
                    } else {
                        $prevAcNo  = $acNo;
                        $prevMonth = $month;   // resets the running month (blank stays blank)
                    }

                    // Only the account number is required — an account with no opening month
                    // is still a real account and must be imported (month left blank).
                    if (!$acNo) {
                        $failed++;
                        $errors[] = ['row' => $rowNum, 'error' => 'Missing ac_no'];
                        continue;
                    }

                    // Parse opening month → date. If the sheet has NO opening month for this
                    // account (or it can't be parsed), leave BOTH month and month_date empty —
                    // never invent a month. The user fills it in later from the edit screen.
                    $monthStr = trim((string) $month);
                    try {
                        $monthDate = $monthStr !== '' ? Carbon::parse('01 ' . $monthStr)->format('Y-m-d') : null;
                    } catch (\Exception $e) {
                        $monthDate = null;
                        $monthStr  = '';
                    }
                    $monthVal = $monthStr !== '' ? $monthStr : null;

                    // Resolve this row's branch first (used for both the card and any auto-created employee)
                    $rowBranch = request()->user()->isBranchManager()
                        ? request()->user()->branch_id
                        : (request()->branch_id ?? null);

                    // New/Sub column: "New" → new, "Sub" → sub, a NUMBER → this is a SUB
                    // account and the number is its parent (main) account.
                    $nsRaw    = trim((string)($row['new_or_sub'] ?? ''));
                    $nsNum    = preg_replace('/\.0$/', '', preg_replace('/[,\s]/', '', $nsRaw));
                    $parentAcc   = null;
                    if ($nsNum !== '' && preg_match('/^\d{2,}$/', $nsNum)) {
                        $accountKind = 'sub'; $parentAcc = $nsNum;
                    } elseif (mb_strtolower($nsRaw) === 'sub') {
                        $accountKind = 'sub';
                    } else {
                        $accountKind = 'new';
                    }

                    // SMART: an account number in ANY employee column (broker first, then marketers)
                    // = a REFERRAL. Its commission becomes the referral commission; the column is cleared.
                    $rv = $row;
                    $refAcc  = trim((string)($rv['referral_account'] ?? ''));
                    $refComm = (float)($rv['referral_commission'] ?? 0);
                    foreach ([['broker','broker_commission'],['marketing','marketing_commission'],
                              ['ext_marketer1','ext_commission1'],['ext_marketer2','ext_commission2']] as $pair) {
                        if ($refAcc !== '') break;
                        $cl = preg_replace('/\.0$/', '', preg_replace('/[,\s]/', '', trim((string)($rv[$pair[0]] ?? ''))));
                        if ($cl !== '' && preg_match('/^\d{2,}$/', $cl)) {
                            $refAcc = $cl; $refComm = (float)($rv[$pair[1]] ?? 0);
                            $rv[$pair[0]] = ''; $rv[$pair[1]] = 0;
                        }
                    }

                    $data = [
                        'month_date'          => $monthDate,
                        'broker_id'           => $ensureEmp($rv['broker']        ?? '', 'broker',    $rowBranch),
                        'broker_commission'   => $commVal($rv['broker_commission']   ?? 0),
                        'marketer_id'         => $ensureEmp($rv['marketing']     ?? '', 'marketing', $rowBranch),
                        'marketer_commission' => $commVal($rv['marketing_commission'] ?? 0),
                        'ext_marketer1_id'    => $ensureEmp($rv['ext_marketer1'] ?? '', 'external',  $rowBranch),  // مسوّق خارجي
                        'ext_commission1'     => $commVal($rv['ext_commission1'] ?? 0),
                        'ext_marketer2_id'    => $ensureEmp($rv['ext_marketer2'] ?? '', 'marketing', $rowBranch),  // مسوّق داخلي 2
                        'ext_commission2'     => $commVal($rv['ext_commission2'] ?? 0),
                        'referral_account'    => $refAcc !== '' ? $refAcc : null,
                        'referral_commission' => $commVal($refComm),
                        'initial_deposit'     => (float)($row['initial_deposit'] ?? 0),
                        'monthly_deposit'     => (float)($row['monthly_deposit'] ?? 0),
                        'account_kind'        => $accountKind,
                        'parent_account'      => $parentAcc,
                        'import_batch_id'     => $batch->id,
                        'created_by'          => request()->user()->id,
                        'status'              => 'active',
                        'branch_id'           => $rowBranch,
                    ];

                    // ── CC import ──
                    if ($mode === 'cc') {
                        $ccFields = [
                            'cc_branch_id'          => $ccBranchId,
                            'cc_agent_id'           => $empLc[$normName($row['cc_agent'] ?? '')] ?? null,
                            'cc_agent_commission'   => $commVal($row['cc_agent_commission'] ?? 0),
                            'cc_manager_commission' => $commVal($row['cc_manager_commission'] ?? 0),
                            'import_batch_id'       => $batch->id,
                        ];

                        // SMART: the same account already imported under a real branch
                        // (duplicate account number from that branch's sheet) → that IS the target.
                        $branchCard = CommissionCard::where('account_number', $acNo)
                                          ->where('month', $month)
                                          ->whereNull('deleted_at')
                                          ->where('branch_id', '!=', $ccBranchId)
                                          ->whereNotNull('branch_id')
                                          ->first();

                        // Explicit "Target Branch" column (used only if no duplicate found)
                        $tgt        = mb_strtolower(trim((string)($row['target_branch'] ?? '')));
                        $explicitId = $tgt !== '' ? ($branchCache[$tgt] ?? null) : null;

                        if ($branchCard) {
                            // Enrich the existing branch card with CC info ONLY — keep its broker/marketer/deposits.
                            $branchCard->update(array_merge($ccFields, ['cc_status' => 'completed']));
                            $linked++;
                            $ccTargets[$branchCard->branch_id] = ($ccTargets[$branchCard->branch_id] ?? 0) + 1;
                            continue;
                        }

                        // No duplicate → CC-only account (or explicit target). Build full record.
                        $targetId = $explicitId;
                        if ($targetId && $targetId !== $ccBranchId) {
                            $ccTargets[$targetId] = ($ccTargets[$targetId] ?? 0) + 1;
                        }
                        $data = array_merge($data, $ccFields, [
                            'branch_id' => $targetId ?: $ccBranchId,
                            'cc_status' => ($targetId && $targetId !== $ccBranchId) ? 'completed' : null,
                            'status'    => 'new_added',
                        ]);
                    }

                    // One card per ACCOUNT NUMBER (an account is opened once) — a repeat
                    // of the same account merges into the existing card, never a duplicate.
                    // One card per ACCOUNT NUMBER **within the same branch** — the same account
                    // number can legitimately exist in different branches (each branch's own sheet),
                    // so scope the dedup to the row's branch (never steal it across branches).
                    $existing = CommissionCard::where('account_number', $acNo)
                                              ->where('branch_id', $rowBranch)
                                              ->whereNull('deleted_at')
                                              ->first();

                    if ($existing) {
                        if ($isCont && $mode !== 'cc') {
                            // Continuation row → MERGE the extra marketer(s) into the card's
                            // empty slots. Never overwrite data already on the card with blanks.
                            $patch = [];
                            // internal marketer(s) coming from this row
                            foreach ([['marketer_id','marketer_commission'], ['ext_marketer2_id','ext_commission2']] as $pair) {
                                $mid = $data[$pair[0]] ?? null;
                                if (!$mid) continue;
                                $taken = [$existing->marketer_id, $existing->ext_marketer2_id,
                                          $patch['marketer_id'] ?? null, $patch['ext_marketer2_id'] ?? null];
                                if (in_array($mid, $taken, true)) continue;          // already on the card
                                if (empty($existing->marketer_id) && !isset($patch['marketer_id'])) {
                                    $patch['marketer_id'] = $mid; $patch['marketer_commission'] = $data[$pair[1]] ?? 0;
                                } elseif (empty($existing->ext_marketer2_id) && !isset($patch['ext_marketer2_id'])) {
                                    $patch['ext_marketer2_id'] = $mid; $patch['ext_commission2'] = $data[$pair[1]] ?? 0;
                                }
                            }
                            // external marketer
                            if (!empty($data['ext_marketer1_id']) && empty($existing->ext_marketer1_id)) {
                                $patch['ext_marketer1_id'] = $data['ext_marketer1_id'];
                                $patch['ext_commission1']  = $data['ext_commission1'] ?? 0;
                            }
                            // broker / referral — fill only if the card is still missing them
                            if (!empty($data['broker_id']) && empty($existing->broker_id)) {
                                $patch['broker_id'] = $data['broker_id'];
                                $patch['broker_commission'] = $data['broker_commission'] ?? 0;
                            }
                            if (!empty($data['referral_account']) && empty($existing->referral_account)) {
                                $patch['referral_account']    = $data['referral_account'];
                                $patch['referral_commission'] = $data['referral_commission'] ?? 0;
                            }
                            if (!empty($patch)) $existing->update($patch);
                            $skipped++;
                        }
                        // Update existing (keep status as-is unless it was inactive)
                        elseif ($existing->status === 'inactive') {
                            $existing->update(array_merge($data, ['status' => 'active']));
                            $skipped++;
                        } else {
                            $existing->update($data);
                            $skipped++;
                        }
                    } else {
                        CommissionCard::create(array_merge($data, [
                            'account_number' => $acNo,
                            'month'          => $monthVal,
                        ]));
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'row'     => $rowNum,
                        'ac_no'   => $row['ac_no'] ?? '?',
                        'error'   => $e->getMessage(),
                    ];
                }
            }

            $batch->update([
                'imported_rows' => $imported,
                'failed_rows'   => $failed,
                'error_log'     => $errors,
                'status'        => ($failed > 0 && $imported === 0 && $skipped === 0 && $linked === 0) ? 'failed' : 'done',
                'finished_at'   => now(),
            ]);
        });

        // ── Invalidate the cards cache so dashboards/reports reflect this import immediately ──
        try {
            \Illuminate\Support\Facades\Cache::forever('cards_cache_ver',
                (int) \Illuminate\Support\Facades\Cache::get('cards_cache_ver', 1) + 1);
        } catch (\Throwable $e) { \Log::warning('cache bump after import failed: '.$e->getMessage()); }

        // ── Notify each target branch's manager(s) that CC cards arrived for them ──
        if ($mode === 'cc' && !empty($ccTargets)) {
            try {
                foreach ($ccTargets as $bid => $cnt) {
                    $managers = \App\Models\User::where('is_active', true)
                        ->where('role', 'branch_manager')
                        ->where('branch_id', $bid)
                        ->where('id', '!=', $request->user()->id)
                        ->get(['id']);
                    if ($managers->isEmpty()) continue;
                    \App\Models\ManagerNotification::fanOut($managers, [
                        'type'           => 'cc_import',
                        'to_branch_id'   => $bid,
                        'from_user_id'   => $request->user()->id,
                        'from_branch_id' => $ccBranchId,
                        'title'          => "وصول {$cnt} حساب من مركز الاتصال",
                        'body'           => "تم استيراد {$cnt} حساب من مركز الاتصال لفرعكم — راجعوها في كروت العمولة.",
                        'data'           => ['count' => $cnt, 'import_batch' => $batchCode],
                    ]);
                }
            } catch (\Throwable $e) { \Log::warning('CC import notify failed: '.$e->getMessage()); }
        }

        ActivityLog::record('import', $batch, [
            'imported'          => $imported,
            'skipped'           => $skipped,
            'linked'            => $linked,
            'failed'            => $failed,
            'created_employees' => $createdEmps,
            'batch'             => $batchCode,
        ]);

        return response()->json([
            'success'           => $failed === 0 || $imported > 0 || $skipped > 0 || $linked > 0,
            'batch_code'        => $batchCode,
            'imported'          => $imported,
            'updated'           => $skipped,
            'linked'            => $linked,
            'created_employees' => $createdEmps,
            'failed'            => $failed,
            'total'             => count($request->rows),
            'errors'            => $errors,
        ]);
    }

    // ── GET /api/import/batches ───────────────────────────────
    public function batches(Request $request): JsonResponse
    {
        $batches = ImportBatch::with('importedBy')
                              ->orderBy('created_at', 'desc')
                              ->paginate(20);

        return response()->json(['success' => true, 'data' => $batches]);
    }
}
