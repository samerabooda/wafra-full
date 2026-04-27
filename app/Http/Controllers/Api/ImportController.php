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
            'rows'         => 'required|array|min:1|max:5000',
            'rows.*.ac_no' => 'required|string',
            'rows.*.month' => 'required|string',
            'filename'     => 'nullable|string|max:255',
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
        $failed   = 0;
        $errors   = [];

        // Employee name → ID cache (single query)
        $empCache = Employee::where('status', 'approved')
                            ->pluck('id', 'name')
                            ->toArray();

        // Pre-load all existing cards for the incoming ac_nos in one query (avoids N+1)
        $incomingAcNos = collect($request->rows)
            ->map(fn($r) => trim((string)($r['ac_no'] ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $existingCards = CommissionCard::whereNull('deleted_at')
            ->whereIn('account_number', $incomingAcNos)
            ->get(['id', 'account_number', 'month', 'status'])
            ->keyBy(fn($c) => $c->account_number . '|' . $c->month);

        $currentUser   = $request->user();
        $branchId      = $currentUser->isBranchManager()
            ? $currentUser->branch_id
            : ($request->branch_id ?? null);

        DB::transaction(function () use ($request, $batch, $empCache, $existingCards, $currentUser, $branchId, &$imported, &$skipped, &$failed, &$errors) {
            foreach ($request->rows as $i => $row) {
                $rowNum = $i + 1;

                try {
                    $acNo  = trim((string)($row['ac_no']  ?? ''));
                    $month = trim((string)($row['month']  ?? ''));

                    if (!$acNo || !$month) {
                        $failed++;
                        $errors[] = ['row' => $rowNum, 'error' => 'Missing ac_no or month'];
                        continue;
                    }

                    // Parse month → date
                    try {
                        $monthDate = Carbon::parse('01 ' . $month)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $monthDate = now()->startOfMonth()->format('Y-m-d');
                    }

                    $data = [
                        'month_date'          => $monthDate,
                        'broker_id'           => $empCache[$row['broker']        ?? ''] ?? null,
                        'broker_commission'   => (float)($row['broker_commission']   ?? 0),
                        'marketer_id'         => $empCache[$row['marketing']      ?? ''] ?? null,
                        'marketer_commission' => (float)($row['marketing_commission'] ?? 0),
                        'ext_marketer1_id'    => $empCache[$row['ext_marketer1']  ?? ''] ?? null,
                        'ext_commission1'     => (float)($row['ext_commission1']  ?? 0),
                        'ext_marketer2_id'    => $empCache[$row['ext_marketer2']  ?? ''] ?? null,
                        'ext_commission2'     => (float)($row['ext_commission2']  ?? 0),
                        'initial_deposit'     => (float)($row['initial_deposit']  ?? 0),
                        'monthly_deposit'     => (float)($row['monthly_deposit']  ?? 0),
                        'account_kind'        => strtolower($row['new_or_sub']    ?? 'new') === 'sub' ? 'sub' : 'new',
                        'import_batch_id'     => $batch->id,
                        'created_by'          => $currentUser->id,
                        'status'              => 'active',
                        'branch_id'           => $branchId,
                    ];

                    // O(1) lookup — no per-row DB query
                    $existing = $existingCards[$acNo . '|' . $month] ?? null;

                    if ($existing) {
                        // Update existing (keep status as-is unless it was inactive)
                        if ($existing->status === 'inactive') {
                            $existing->update(array_merge($data, ['status' => 'active']));
                        } else {
                            $existing->update($data);
                        }
                        $skipped++;
                    } else {
                        CommissionCard::create(array_merge($data, [
                            'account_number' => $acNo,
                            'month'          => $month,
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
                'status'        => ($failed > 0 && $imported === 0 && $skipped === 0) ? 'failed' : 'done',
                'finished_at'   => now(),
            ]);
        });

        ActivityLog::record('import', $batch, [
            'imported' => $imported,
            'skipped'  => $skipped,
            'failed'   => $failed,
            'batch'    => $batchCode,
        ]);

        return response()->json([
            'success'    => $failed === 0 || $imported > 0 || $skipped > 0,
            'batch_code' => $batchCode,
            'imported'   => $imported,
            'updated'    => $skipped,
            'failed'     => $failed,
            'total'      => count($request->rows),
            'errors'     => $errors,
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
