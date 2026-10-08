<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, ImportBatch, Employee, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator};
use Carbon\Carbon;

/**
 * ImportController — Excel bulk import of commission cards
 *
 * RULES (agreed with Salah):
 *  A. Name matching:  case + whitespace tolerant
 *  B. Missing broker/marketer → Self (per-branch employee, auto-created)
 *     Unknown employee name → auto-created in target branch, status=approved
 *  C. Marketing/ExtMkt1/ExtMkt2 = number → referral_account (max ONE per card)
 *     Unknown referral account → accepted (stored as-is text)
 *  D. $7 commission ceiling → import anyway, flag is_over_limit=true (red row + review table)
 *  E. Duplicate (same account_number + month) → import anyway, flag is_duplicate=true
 */
class ImportController extends Controller
{
    /** Global commission ceiling per card ($/lot) */
    const COMMISSION_LIMIT = 7.0;

    /**
     * POST /api/import
     * Body: { rows: [...], filename?: string, branch_id?: int }
     */
    public function import(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'rows'         => 'required|array|min:1',
            'rows.*.ac_no' => 'required|string',
            'rows.*.month' => 'required|string',
            'filename'     => 'nullable|string|max:255',
            'branch_id'    => 'nullable|integer|exists:branches,id',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user = $request->user();

        // Determine the target branch: FA passes it; branch manager uses their own
        $targetBranchId = $user->isBranchManager()
            ? $user->branch_id
            : (int) ($request->branch_id ?? 0);

        if (!$targetBranchId) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تحديد الفرع المستهدف قبل الاستيراد.',
            ], 422);
        }

        $batchCode = 'IMP-' . now()->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));

        $batch = ImportBatch::create([
            'batch_code'  => $batchCode,
            'filename'    => $request->filename ?? 'upload-' . now()->format('YmdHis'),
            'total_rows'  => count($request->rows),
            'status'      => 'processing',
            'imported_by' => $user->id,
            'started_at'  => now(),
        ]);

        // Load approved employees ONCE — normalised keys for flexible matching
        $empCache = $this->buildEmployeeCache();

        $stats = ['imported' => 0, 'updated' => 0, 'failed' => 0,
                  'over_limit' => 0, 'duplicates' => 0, 'auto_created' => 0];
        $errors = [];

        DB::transaction(function () use (
            $request, $batch, $targetBranchId, &$empCache,
            &$stats, &$errors, $user
        ) {
            foreach ($request->rows as $i => $row) {
                $rowNum = $i + 1;

                try {
                    $result = $this->processRow(
                        $row, $rowNum, $targetBranchId,
                        $empCache, $user, $batch, $stats
                    );

                    if ($result === 'imported')  $stats['imported']++;
                    elseif ($result === 'updated') $stats['updated']++;

                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $errors[] = [
                        'row'   => $rowNum,
                        'ac_no' => $row['ac_no'] ?? '?',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $batch->update([
                'imported_rows' => $stats['imported'] + $stats['updated'],
                'failed_rows'   => $stats['failed'],
                'error_log'     => $errors,
                'status'        => ($stats['failed'] > 0 && $stats['imported'] === 0 && $stats['updated'] === 0)
                                   ? 'failed' : 'done',
                'finished_at'   => now(),
            ]);
        });

        ActivityLog::record('import', $batch, [
            'branch_id'    => $targetBranchId,
            'batch'        => $batchCode,
            'imported'     => $stats['imported'],
            'updated'      => $stats['updated'],
            'failed'       => $stats['failed'],
            'over_limit'   => $stats['over_limit'],
            'duplicates'   => $stats['duplicates'],
            'auto_created' => $stats['auto_created'],
        ]);

        return response()->json([
            'success'       => $stats['failed'] === 0 || $stats['imported'] > 0 || $stats['updated'] > 0,
            'batch_code'    => $batchCode,
            'imported'      => $stats['imported'],
            'updated'       => $stats['updated'],
            'failed'        => $stats['failed'],
            'over_limit'    => $stats['over_limit'],
            'duplicates'    => $stats['duplicates'],
            'auto_created'  => $stats['auto_created'],
            'total'         => count($request->rows),
            'errors'        => $errors,
        ]);
    }

    /**
     * Process one Excel row into a commission card.
     * Returns 'imported' or 'updated'.
     */
    private function processRow(array $row, int $rowNum, int $branchId,
                                array &$empCache, $user, $batch, array &$stats): string
    {
        $acNo  = trim((string) ($row['ac_no'] ?? ''));
        $month = trim((string) ($row['month'] ?? ''));

        if (!$acNo || !$month) {
            throw new \RuntimeException("رقم الحساب أو الشهر مفقود (صف {$rowNum})");
        }

        // ── Parse month → date ───────────────────────────────────
        try {
            $monthDate = Carbon::parse('01 ' . $month)->format('Y-m-d');
        } catch (\Throwable $e) {
            $monthDate = now()->startOfMonth()->format('Y-m-d');
        }

        // ── Resolve Broker (name or Self) ────────────────────────
        $brokerName    = trim((string) ($row['broker'] ?? ''));
        $brokerComm    = (float) ($row['broker_commission'] ?? 0);
        $brokerId      = $this->resolveEmployee($brokerName, 'broker', $branchId, $brokerComm, $empCache, $stats);

        // ── Resolve Marketing (name/number/empty) ────────────────
        $mktRaw        = trim((string) ($row['marketing'] ?? ''));
        $mktComm       = (float) ($row['marketing_commission'] ?? 0);
        $marketerId    = null;
        $referralAccount = null;
        $referralComm  = 0.0;

        if ($this->looksLikeAccountNumber($mktRaw)) {
            // It's a referral
            $referralAccount = $mktRaw;
            $referralComm    = $mktComm;
        } else {
            // Internal marketer (name or empty → Self)
            $marketerId = $this->resolveEmployee($mktRaw, 'marketing', $branchId, $mktComm, $empCache, $stats);
        }

        // ── Resolve Ext Marketer 1 ───────────────────────────────
        $ext1Raw  = trim((string) ($row['ext_marketer1'] ?? ''));
        $ext1Comm = (float) ($row['ext_commission1'] ?? 0);
        $ext1Id   = null;
        if ($this->looksLikeAccountNumber($ext1Raw)) {
            if ($referralAccount) {
                throw new \RuntimeException("لا يمكن كتابة أكثر من حساب محيل واحد في الكرت (صف {$rowNum})");
            }
            $referralAccount = $ext1Raw;
            $referralComm    = $ext1Comm;
        } elseif ($ext1Raw !== '') {
            $ext1Id = $this->resolveEmployee($ext1Raw, 'external', $branchId, $ext1Comm, $empCache, $stats);
        }

        // ── Resolve Ext Marketer 2 ───────────────────────────────
        $ext2Raw  = trim((string) ($row['ext_marketer2'] ?? ''));
        $ext2Comm = (float) ($row['ext_commission2'] ?? 0);
        $ext2Id   = null;
        if ($this->looksLikeAccountNumber($ext2Raw)) {
            if ($referralAccount) {
                throw new \RuntimeException("لا يمكن كتابة أكثر من حساب محيل واحد في الكرت (صف {$rowNum})");
            }
            $referralAccount = $ext2Raw;
            $referralComm    = $ext2Comm;
        } elseif ($ext2Raw !== '') {
            $ext2Id = $this->resolveEmployee($ext2Raw, 'external', $branchId, $ext2Comm, $empCache, $stats);
        }

        // ── Compute total commission and flags ───────────────────
        $totalComm  = $brokerComm + $mktComm + $ext1Comm + $ext2Comm + $referralComm;
        $overLimit  = $totalComm > self::COMMISSION_LIMIT;
        if ($overLimit) $stats['over_limit']++;

        // ── Check duplicate (both live and soft-deleted so we don't crash
        //    the unique constraint on insert) ────────────────────
        $existing = CommissionCard::withTrashed()
                                  ->where('account_number', $acNo)
                                  ->where('month', $month)
                                  ->first();

        // Only flag "duplicate" when the existing row is LIVE — a soft-deleted
        // row being resurrected is a legitimate re-import, not a dupe.
        $isDuplicate = $existing && $existing->deleted_at === null;
        if ($isDuplicate) $stats['duplicates']++;

        // ── Build the card data ──────────────────────────────────
        $data = [
            'month_date'          => $monthDate,
            'broker_id'           => $brokerId,
            'broker_commission'   => $brokerComm,
            'marketer_id'         => $marketerId,
            'marketer_commission' => $mktComm,
            'ext_marketer1_id'    => $ext1Id,
            'ext_commission1'     => $ext1Comm,
            'ext_marketer2_id'    => $ext2Id,
            'ext_commission2'     => $ext2Comm,
            'referral_account'    => $referralAccount,
            'referral_commission' => $referralComm,
            'initial_deposit'     => (float) ($row['initial_deposit'] ?? 0),
            'monthly_deposit'     => (float) ($row['monthly_deposit'] ?? 0),
            'account_kind'        => strtolower($row['new_or_sub'] ?? 'new') === 'sub' ? 'sub' : 'new',
            'import_batch_id'     => $batch->id,
            'created_by'          => $user->id,
            'branch_id'           => $branchId,
            'status'              => 'active',
            'is_over_limit'       => $overLimit,
            'is_duplicate'        => $isDuplicate,
        ];

        if ($existing) {
            // Overwrite fields (keep created_by from original).
            // Reset the duplicate flag on UPDATE — a re-import of the same
            // card shouldn't be marked as "needs review" every time.
            $data['status']       = $existing->status === 'inactive' ? 'active' : $existing->status;
            $data['is_duplicate'] = false;
            unset($data['created_by']);

            // Resurrect if it was soft-deleted (so the unique constraint is happy)
            if ($existing->deleted_at !== null) {
                $existing->restore();
            }
            $existing->update($data);
            return 'updated';
        }

        CommissionCard::create(array_merge($data, [
            'account_number' => $acNo,
            'month'          => $month,
        ]));
        return 'imported';
    }

    /**
     * Build a cache of employees keyed by normalised name → id.
     * Case + whitespace tolerant lookup.
     */
    private function buildEmployeeCache(): array
    {
        $cache = [];
        Employee::where('status', 'approved')
                ->whereNull('deleted_at')
                ->select('id', 'name', 'branch_id')
                ->get()
                ->each(function ($e) use (&$cache) {
                    $key = $this->normaliseName($e->name);
                    // Keep first match; branch-scoped lookup happens at resolve time
                    if (!isset($cache[$key])) $cache[$key] = $e->id;
                });
        return $cache;
    }

    private function normaliseName(string $name): string
    {
        // Lowercase + collapse whitespace + trim
        return preg_replace('/\s+/u', ' ', mb_strtolower(trim($name)));
    }

    private function looksLikeAccountNumber(string $value): bool
    {
        return $value !== '' && preg_match('/^\d{3,}$/', $value) === 1;
    }

    /**
     * Look up an employee by name (flexible match) — or auto-create if missing.
     * Empty name → returns the branch's "Self" employee.
     */
    private function resolveEmployee(string $name, string $role, int $branchId,
                                     float $commission, array &$empCache, array &$stats): int
    {
        $name = trim($name);

        // Empty OR literally "Self" (any casing) → branch Self
        $normalised = $this->normaliseName($name);
        if ($name === '' || $normalised === 'self') {
            return $this->getOrCreateSelf($branchId, $empCache);
        }

        $key = $normalised;

        if (isset($empCache[$key])) {
            return $empCache[$key];
        }

        // Not found → create as approved in target branch
        $roleMap = [
            'broker'    => 'broker',
            'marketing' => 'marketing',
            'external'  => 'external',
        ];
        $emp = Employee::create([
            'name'                 => $name,
            'role'                 => $roleMap[$role] ?? 'other',
            'branch_id'            => $branchId,
            'broker_commission'    => $role === 'broker'    ? $commission : 0,
            'marketing_commission' => $role !== 'broker'    ? $commission : 0,
            'status'               => 'approved',
            'is_active'            => true,
            'is_base'              => false,
        ]);

        $empCache[$key] = $emp->id;
        $stats['auto_created']++;

        return $emp->id;
    }

    /**
     * Get (or create) the branch's "Self" employee.
     * One "Self" per branch — represents accounts without a broker/marketer.
     */
    private function getOrCreateSelf(int $branchId, array &$empCache): int
    {
        $selfName = "Self";
        $key = $this->normaliseName($selfName) . "|br={$branchId}";

        if (isset($empCache[$key])) {
            return $empCache[$key];
        }

        // Check DB for existing Self in this branch
        $existing = Employee::where('name', $selfName)
                            ->where('branch_id', $branchId)
                            ->whereNull('deleted_at')
                            ->first();

        if ($existing) {
            $empCache[$key] = $existing->id;
            return $existing->id;
        }

        // Create one
        $self = Employee::create([
            'name'                 => $selfName,
            'role'                 => 'other',
            'branch_id'            => $branchId,
            'broker_commission'    => 0,
            'marketing_commission' => 0,
            'status'               => 'approved',
            'is_active'            => true,
            'is_base'              => false,
        ]);

        $empCache[$key] = $self->id;
        return $self->id;
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
