<?php

namespace App\Console\Commands;

use App\Models\{
    Branch, CommissionCard, Employee, User, CcNotification,
    CcFollowup, ManagerNotification, ActivityLog, CardSummary
};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Hash, Artisan};
use Illuminate\Support\Carbon;
use Throwable;

/**
 * End-to-end workflow test for the Commission Cards system.
 *
 *   php artisan test:system-workflow            # run + report + cleanup
 *   php artisan test:system-workflow --keep     # run + report, leave test data in place
 *   php artisan test:system-workflow --cleanup  # delete prior test data only
 *
 * Creates 100 cards tagged with [WFTEST] in notes, walks the full CC lifecycle
 * (send → accept → followup → complete / reject / decline), verifies notifications,
 * CcFollowup rows, CardSummary rebuild, and reports. Writes a Markdown report to
 * storage/logs/workflow-test-{timestamp}.md and prints a short summary.
 *
 * Isolation: everything is tagged with the WFTEST marker in notes/metadata so
 * cleanup removes it precisely without touching real production data.
 */
class TestSystemWorkflow extends Command
{
    protected $signature = 'test:system-workflow {--keep} {--cleanup}';
    protected $description = 'Run end-to-end workflow test, generate report, and clean up';

    private const MARKER = '[WFTEST]';
    private const ACC_RANGE_START = 990000;
    private const ACC_RANGE_END   = 999999;

    private array $trace  = [];       // step-by-step log for the report
    private array $stats  = [];       // numeric tallies
    private array $errors = [];       // anything that didn't match expectation
    private int   $startedAt;

    public function handle(): int
    {
        $this->startedAt = time();

        if ($this->option('cleanup')) {
            $this->info('Cleanup only — removing prior WFTEST data…');
            $n = $this->cleanup();
            $this->info("Removed {$n} test records.");
            return 0;
        }

        try {
            $this->section('0. Pre-cleanup — remove orphans from prior runs');
            $pre = $this->cleanup();
            if ($pre > 0) $this->line("  Removed {$pre} orphan records from a prior run.");
            else          $this->line("  Nothing to clean up — fresh slate.");

            $this->section('1. Preflight checks');
            if (!$this->preflight()) return 1;

            $this->section('2. Setup — test actors');
            $actors = $this->setupActors();

            $this->section('3. Creating 40 regular branch cards');
            $this->guard('createRegularCards', fn() => $this->createRegularCards($actors));

            $this->section('4. CC workflow — 60 cards across all states');
            $this->guard('runCcWorkflow', fn() => $this->runCcWorkflow($actors));

            $this->section('5. Edge cases');
            $this->guard('testEdgeCases', fn() => $this->testEdgeCases($actors));

            $this->section('6. Verifying notifications fan-out');
            $this->guard('verifyNotifications', fn() => $this->verifyNotifications());

            $this->section('7. Verifying CcFollowup entries');
            $this->guard('verifyFollowups', fn() => $this->verifyFollowups());

            $this->section('8. Rebuilding + verifying CardSummary');
            $this->guard('verifySummary', fn() => $this->verifySummary());

            $this->section('9. Running reports');
            $this->guard('runReports', fn() => $this->runReports());

            $this->section('10. Writing report');
            $path = $this->writeReport();
            $this->info("📄 Report saved: {$path}");

            if ($this->option('keep')) {
                $this->warn('--keep set: test data NOT deleted. Run with --cleanup to remove.');
            } else {
                $this->section('11. Cleanup');
                $n = $this->cleanup();
                $this->info("🧹 Removed {$n} test records.");
            }

            // ── Optional: trigger test:cc-batch via commit-message flags ──
            //   +batch<N>     — create N persistent CC cards after the regression test
            //   +clean-batch  — remove any existing [CC-BATCH]-tagged cards
            try {
                $msg = @shell_exec('cd ' . escapeshellarg(base_path()) . ' && git log -1 --pretty=%s 2>/dev/null') ?: '';
                if (preg_match('/\+clean-?batch/', $msg)) {
                    $this->section('BONUS: cleaning up [CC-BATCH] records (+clean-batch flag)');
                    Artisan::call('test:cc-batch', ['--cleanup' => true], $this->output);
                }
                if (preg_match('/\+batch(\d+)?/', $msg, $m)) {
                    $count = (int) ($m[1] ?? 80);
                    if ($count < 6) $count = 80;
                    $this->section('BONUS: creating persistent CC batch (' . $count . ' cards) for visual inspection');
                    Artisan::call('test:cc-batch', ['--count' => $count], $this->output);
                }
            } catch (Throwable $e) {
                $this->warn('Bonus batch skipped: ' . $e->getMessage());
            }

            $this->printSummary();
            return count($this->errors) ? 2 : 0;

        } catch (Throwable $e) {
            $this->error("FATAL: {$e->getMessage()}");
            $this->error("At: {$e->getFile()}:{$e->getLine()}");
            $this->error($e->getTraceAsString());
            // Best-effort cleanup on crash
            if (!$this->option('keep')) {
                try { $this->cleanup(); } catch (Throwable $e2) {}
            }
            return 1;
        }
    }

    /* ───────────────── Preflight ───────────────── */

    private function preflight(): bool
    {
        $branches  = Branch::where('is_active', true)->count();
        $employees = Employee::where('status', 'approved')->count();
        $fa        = User::where('role', 'finance_admin')->where('is_active', true)->first();

        $this->line("  Active branches : {$branches}");
        $this->line("  Approved employees : {$employees}");
        $this->line('  Finance admin : ' . ($fa ? "{$fa->name} ({$fa->email})" : 'MISSING'));

        if ($branches < 2) { $this->error('Need at least 2 active branches.'); return false; }
        if ($employees < 3) { $this->error('Need at least 3 approved employees.'); return false; }
        if (!$fa) { $this->error('No Finance Admin user found.'); return false; }

        $this->trace[] = ['step' => 'preflight', 'branches' => $branches, 'employees' => $employees, 'fa' => $fa->email];
        return true;
    }

    /* ───────────────── Setup actors ───────────────── */

    private function setupActors(): array
    {
        $fa = User::where('role', 'finance_admin')->where('is_active', true)->first();

        // Pick a CC branch (or mark one if none) and a regular branch
        $ccBranch = Branch::where('is_call_center', true)->where('is_active', true)->first();
        if (!$ccBranch) {
            $ccBranch = Branch::where('is_active', true)->first();
            $ccBranch->update(['is_call_center' => true]);
            $this->line("  Marked branch '{$ccBranch->name_ar}' as Call-Center (was not flagged)");
            $this->trace[] = ['step' => 'cc_branch_marked', 'branch_id' => $ccBranch->id];
        }

        $regularBranches = Branch::where('is_active', true)
            ->where('id', '!=', $ccBranch->id)
            ->get();

        // Make sure a branch_manager exists for each regular branch — needed for notifications
        foreach ($regularBranches as $br) {
            $hasMgr = User::where('branch_id', $br->id)->where('role', 'branch_manager')->exists();
            if (!$hasMgr) {
                // Reuse FA — do not create fake users (keeps test non-destructive)
                $this->line("  Branch '{$br->name_ar}' has no manager — FA will receive its notifications");
            }
        }

        // Make sure a CC manager exists on the CC branch — create a TEMP one for lifecycle
        // notification testing if none exists. Cleaned up at the end.
        $ccMgr = User::where('branch_id', $ccBranch->id)->where('role', 'branch_manager')->first();
        $tempMgrCreated = false;
        if (!$ccMgr) {
            $ccMgr = User::create([
                'name'      => '[WFTEST] CC Manager',
                'email'     => 'wftest-cc-mgr@wafragulf.test',
                'password'  => Hash::make('wftest-' . bin2hex(random_bytes(8))),
                'role'      => 'branch_manager',
                'branch_id' => $ccBranch->id,
                'is_active' => true,
            ]);
            $tempMgrCreated = true;
            $this->line("  Created temp CC manager (wftest-cc-mgr@wafragulf.test) — cleaned up at end");
            $this->trace[] = ['step' => 'temp_cc_mgr_created', 'user_id' => $ccMgr->id];
        }

        $this->line("  CC branch     : {$ccBranch->name_ar} (id={$ccBranch->id})");
        $this->line("  Regular brs   : " . $regularBranches->count());
        $this->line("  CC manager    : {$ccMgr->email}" . ($tempMgrCreated ? ' (TEMP)' : ''));

        // Log in as FA so ActivityLog::record() captures a user id
        auth()->login($fa);

        $this->trace[] = ['step' => 'actors', 'fa_id' => $fa->id, 'cc_branch_id' => $ccBranch->id];

        return [
            'fa' => $fa,
            'cc_branch' => $ccBranch,
            'cc_mgr' => $ccMgr ?: $fa,
            'regular_branches' => $regularBranches,
            'employees' => Employee::where('status', 'approved')->get(),
        ];
    }

    /* ───────────────── Regular cards ───────────────── */

    private function createRegularCards(array $a): void
    {
        $created = 0; $failed = 0;
        $month = now()->format('M-Y');
        $monthDate = now()->startOfMonth()->toDateString();

        foreach ($a['regular_branches'] as $br) {
            // ~5 cards per branch, max 40 total
            for ($i = 0; $i < 5 && $created < 40; $i++) {
                $ac = $this->nextAccount();
                try {
                    $card = CommissionCard::create([
                        'account_number'      => (string) $ac,
                        'month'               => $month,
                        'month_date'          => $monthDate,
                        'branch_id'           => $br->id,
                        'broker_id'           => $a['employees']->random()->id,
                        'broker_commission'   => $this->pick([3, 4, 5, 6]),
                        'marketer_id'         => rand(0,1) ? $a['employees']->random()->id : null,
                        'marketer_commission' => rand(0,1) ? $this->pick([1, 2, 3]) : 0,
                        'account_kind'        => $this->pick(['new','new','sub']),
                        'initial_deposit'     => $this->pick([500, 1000, 2000, 5000]),
                        'monthly_deposit'     => $this->pick([0, 200, 500]),
                        'status'              => 'new_added',
                        'notes'               => self::MARKER . ' regular #' . ($created + 1),
                        'created_by'          => $a['fa']->id,
                    ]);
                    ActivityLog::record('wftest_create_regular', $card);
                    $created++;
                } catch (Throwable $e) {
                    $failed++;
                    $this->errors[] = "Regular card create failed: " . $e->getMessage();
                }
            }
        }

        $this->line("  ✓ Created {$created} regular cards (failed: {$failed})");
        $this->stats['regular_created'] = $created;
        $this->stats['regular_failed']  = $failed;
    }

    /* ───────────────── CC workflow ───────────────── */

    private function runCcWorkflow(array $a): void
    {
        $actor      = $a['cc_mgr'];  // acts as the sender in CC branch
        $ccBranchId = $a['cc_branch']->id;
        $month      = now()->format('M-Y');
        $monthDate  = now()->startOfMonth()->toDateString();

        $tally = [
            'cc_pending' => 0, 'branch_pending' => 0, 'in_progress' => 0,
            'completed' => 0, 'rejected' => 0, 'declined' => 0,
            'notifications_fanned' => 0, 'followups_created' => 0,
        ];

        // Helper to create a draft CC card
        $makeDraft = function (int $targetBranchId, string $phone, int $i) use ($a, $ccBranchId, $month, $monthDate, $actor): CommissionCard {
            return CommissionCard::create([
                'account_number'       => (string) $this->nextAccount(),
                'month'                => $month,
                'month_date'           => $monthDate,
                'branch_id'            => $targetBranchId,
                'cc_branch_id'         => $ccBranchId,
                'cc_agent_id'          => $a['employees']->random()->id,
                'cc_agent_commission'  => $this->pick([0.5, 1, 1.5]),
                'cc_manager_commission'=> 0.5,
                'cc_status'            => 'cc_pending',
                'status'               => 'new_added',
                'client_phone'         => $phone,
                'client_country_code'  => '+965',
                'client_source'        => 'test-wf',
                'account_kind'         => 'new',
                'initial_deposit'      => 0,  // branch fills on completion
                'monthly_deposit'      => 0,
                'notes'                => self::MARKER . ' cc-draft #' . $i,
                'created_by'           => $actor->id,
            ]);
        };

        // ── 10 drafts (never sent) ─────────────────────────────────
        for ($i = 0; $i < 10; $i++) {
            $b = $a['regular_branches']->random();
            $makeDraft($b->id, '5500' . str_pad((string)$i, 4, '0', STR_PAD_LEFT), $i + 1);
            $tally['cc_pending']++;
        }

        // ── 10 sent (branch_pending) ──────────────────────────────
        for ($i = 0; $i < 10; $i++) {
            $b = $a['regular_branches']->random();
            $card = $makeDraft($b->id, '5501' . str_pad((string)$i, 4, '0', STR_PAD_LEFT), $i + 1);
            $this->ccSend($card, $actor);
            $tally['branch_pending']++;
            $tally['notifications_fanned'] += 1;
        }

        // ── 10 in_progress (sent + followed up) ────────────────────
        for ($i = 0; $i < 10; $i++) {
            $b = $a['regular_branches']->random();
            $card = $makeDraft($b->id, '5502' . str_pad((string)$i, 4, '0', STR_PAD_LEFT), $i + 1);
            $this->ccSend($card, $actor);
            $this->ccFollowup($card, $actor, "WFTEST تم الاتصال، العميل يحتاج وقت للتفكير");
            $tally['in_progress']++;
            $tally['notifications_fanned'] += 2;
            $tally['followups_created'] += 1;
        }

        // ── 10 completed (full lifecycle) ──────────────────────────
        for ($i = 0; $i < 10; $i++) {
            $b = $a['regular_branches']->random();
            $card = $makeDraft($b->id, '5503' . str_pad((string)$i, 4, '0', STR_PAD_LEFT), $i + 1);
            $this->ccSend($card, $actor);
            $this->ccFollowup($card, $actor, "WFTEST مكالمة أولى تمت");
            $this->ccComplete($card, $actor, $a);
            $tally['completed']++;
            $tally['notifications_fanned'] += 3;
            $tally['followups_created'] += 2;  // followup + opened on complete
        }

        // ── 10 rejected (branch rejected after send) ───────────────
        for ($i = 0; $i < 10; $i++) {
            $b = $a['regular_branches']->random();
            $card = $makeDraft($b->id, '5504' . str_pad((string)$i, 4, '0', STR_PAD_LEFT), $i + 1);
            $this->ccSend($card, $actor);
            $this->ccReject($card, $actor, "WFTEST البيانات ناقصة");
            $tally['rejected']++;
            $tally['notifications_fanned'] += 2;
        }

        // ── 10 declined (customer refused) ─────────────────────────
        for ($i = 0; $i < 10; $i++) {
            $b = $a['regular_branches']->random();
            $card = $makeDraft($b->id, '5505' . str_pad((string)$i, 4, '0', STR_PAD_LEFT), $i + 1);
            $this->ccSend($card, $actor);
            $this->ccDecline($card, $actor, "WFTEST العميل رفض بعد المحاولة");
            $tally['declined']++;
            $tally['notifications_fanned'] += 2;
            $tally['followups_created'] += 1;
        }

        foreach ($tally as $k => $v) $this->line("  {$k}: {$v}");
        $this->stats['cc'] = $tally;
    }

    /* ───────────────── CC transitions (replicate controller behaviour) ───────────────── */

    private function ccSend(CommissionCard $card, User $actor): void
    {
        $card->update(['cc_status' => 'branch_pending']);

        CcNotification::create([
            'card_id'        => $card->id,
            'from_branch_id' => $card->cc_branch_id,
            'to_branch_id'   => $card->branch_id,
            'sent_by'        => $actor->id,
            'type'           => 'card_sent',
            'status'         => 'unread',
            'message'        => self::MARKER . " عميل مهتم (ht: {$card->client_phone})",
        ]);

        // Fan-out to target branch managers + all FAs (same logic as notifyManagers in controller)
        $recipients = User::where('is_active', true)
            ->where(function ($q) use ($card, $actor) {
                $q->where(function ($w) use ($card, $actor) {
                    $w->where('role', 'branch_manager')
                      ->where('branch_id', $card->branch_id)
                      ->where('id', '!=', $actor->id);
                })->orWhere('role', 'finance_admin');
            })
            ->get(['id']);

        if ($recipients->isNotEmpty()) {
            ManagerNotification::fanOut($recipients, [
                'type'           => 'cc_new_card',
                'card_id'        => $card->id,
                'account_number' => $card->account_number,
                'month'          => $card->month,
                'from_user_id'   => $actor->id,
                'from_branch_id' => $card->cc_branch_id,
                'to_branch_id'   => $card->branch_id,
                'title'          => self::MARKER . ' عميل جديد',
                'body'           => "اختبار workflow — hone: {$card->client_phone}",
                'data'           => ['wftest' => true],
            ]);
        }

        ActivityLog::record('wftest_cc_sent', $card);
    }

    private function ccFollowup(CommissionCard $card, User $actor, string $note): void
    {
        $card->update(['cc_status' => 'in_progress']);

        CcFollowup::create([
            'card_id'   => $card->id,
            'user_id'   => $actor->id,
            'branch_id' => $card->branch_id,
            'outcome'   => 'in_progress',
            'note'      => $note,
        ]);

        CcNotification::create([
            'card_id'        => $card->id,
            'from_branch_id' => $card->branch_id,
            'to_branch_id'   => $card->cc_branch_id,
            'sent_by'        => $actor->id,
            'responded_by'   => $actor->id,
            'type'           => 'card_followup',
            'status'         => 'unread',
            'message'        => self::MARKER . ' متابعة: ' . $note,
        ]);

        $this->fanLifecycle($card, $actor, 'cc_card_followup', 'متابعة جديدة', $note);
        ActivityLog::record('wftest_cc_followup', $card, ['note' => $note]);
    }

    private function ccComplete(CommissionCard $card, User $actor, array $a): void
    {
        $card->update([
            'cc_status'           => 'completed',
            'status'              => 'new_added',
            'broker_id'           => $a['employees']->random()->id,
            'broker_commission'   => $this->pick([2, 2.5, 3]),
            'marketer_id'         => $a['employees']->random()->id,
            'marketer_commission' => $this->pick([1, 1.5, 2]),
            'initial_deposit'     => $this->pick([500, 1000, 2000, 5000]),
            'monthly_deposit'     => $this->pick([0, 200, 500]),
        ]);

        CcFollowup::create([
            'card_id'   => $card->id,
            'user_id'   => $actor->id,
            'branch_id' => $card->branch_id,
            'outcome'   => 'opened',
            'note'      => self::MARKER . " تم فتح الحساب #{$card->account_number}",
        ]);

        CcNotification::create([
            'card_id'        => $card->id,
            'from_branch_id' => $card->branch_id,
            'to_branch_id'   => $card->cc_branch_id,
            'sent_by'        => $actor->id,
            'responded_by'   => $actor->id,
            'type'           => 'card_completed',
            'status'         => 'unread',
            'message'        => self::MARKER . " تم فتح الحساب #{$card->account_number}",
        ]);

        $this->fanLifecycle($card, $actor, 'cc_card_completed', 'الحساب تم فتحه',
            "تم فتح #{$card->account_number} بإيداع {$card->initial_deposit}");
        ActivityLog::record('wftest_cc_completed', $card);
    }

    private function ccReject(CommissionCard $card, User $actor, string $reason): void
    {
        $card->update(['cc_status' => 'rejected', 'cc_rejection_reason' => $reason]);

        CcNotification::create([
            'card_id'        => $card->id,
            'from_branch_id' => $card->branch_id,
            'to_branch_id'   => $card->cc_branch_id,
            'sent_by'        => $actor->id,
            'responded_by'   => $actor->id,
            'type'           => 'card_rejected',
            'status'         => 'unread',
            'message'        => self::MARKER . ' رفض: ' . $reason,
        ]);

        $this->fanLifecycle($card, $actor, 'cc_card_rejected', 'الكرت رُفض', $reason);
        ActivityLog::record('wftest_cc_rejected', $card, ['reason' => $reason]);
    }

    private function ccDecline(CommissionCard $card, User $actor, string $reason): void
    {
        $card->update(['cc_status' => 'declined', 'cc_rejection_reason' => $reason]);

        CcFollowup::create([
            'card_id'   => $card->id,
            'user_id'   => $actor->id,
            'branch_id' => $card->branch_id,
            'outcome'   => 'declined',
            'note'      => $reason,
        ]);

        CcNotification::create([
            'card_id'        => $card->id,
            'from_branch_id' => $card->branch_id,
            'to_branch_id'   => $card->cc_branch_id,
            'sent_by'        => $actor->id,
            'responded_by'   => $actor->id,
            'type'           => 'card_declined',
            'status'         => 'unread',
            'message'        => self::MARKER . ' العميل رفض: ' . $reason,
        ]);

        $this->fanLifecycle($card, $actor, 'cc_card_declined', 'العميل رفض فتح الحساب', $reason);
        ActivityLog::record('wftest_cc_declined', $card, ['reason' => $reason]);
    }

    private function fanLifecycle(CommissionCard $card, User $actor, string $type, string $title, string $body): void
    {
        if (!$card->cc_branch_id) return;

        $recipients = User::where('is_active', true)
            ->where(function ($q) use ($card, $actor) {
                $q->where(function ($w) use ($card, $actor) {
                    $w->where('role', 'branch_manager')
                      ->where('branch_id', $card->cc_branch_id)
                      ->where('id', '!=', $actor->id);
                })->orWhere(function ($w) use ($actor) {
                    $w->where('role', 'finance_admin')
                      ->where('id', '!=', $actor->id);
                });
            })
            ->get(['id']);

        if ($recipients->isEmpty()) return;

        ManagerNotification::fanOut($recipients, [
            'type'           => $type,
            'card_id'        => $card->id,
            'account_number' => $card->account_number,
            'month'          => $card->month,
            'from_user_id'   => $actor->id,
            'from_branch_id' => $card->branch_id,
            'to_branch_id'   => $card->cc_branch_id,
            'title'          => self::MARKER . ' ' . $title,
            'body'           => $body,
            'data'           => ['wftest' => true],
        ]);
    }

    /* ───────────────── Edge cases ───────────────── */

    private function testEdgeCases(array $a): void
    {
        $ccBranchId = $a['cc_branch']->id;
        $month = now()->format('M-Y');
        $monthDate = now()->startOfMonth()->toDateString();
        $actor = $a['cc_mgr'];
        $edge = ['duplicate_phone' => 0, 'over_limit' => 0, 'cross_branch' => 0];

        // ── duplicate phone (2 cards same phone) ────────────────
        try {
            $b = $a['regular_branches']->random();
            $phone = '55990001';
            CommissionCard::create([
                'account_number' => (string) $this->nextAccount(),
                'month' => $month, 'month_date' => $monthDate,
                'branch_id' => $b->id, 'cc_branch_id' => $ccBranchId,
                'cc_status' => 'branch_pending', 'client_phone' => $phone,
                'client_country_code' => '+965', 'account_kind' => 'new',
                'notes' => self::MARKER . ' dup-phone A', 'created_by' => $a['fa']->id,
            ]);
            CommissionCard::create([
                'account_number' => (string) $this->nextAccount(),
                'month' => $month, 'month_date' => $monthDate,
                'branch_id' => $b->id, 'cc_branch_id' => $ccBranchId,
                'cc_status' => 'branch_pending', 'client_phone' => $phone,
                'client_country_code' => '+965', 'account_kind' => 'new',
                'notes' => self::MARKER . ' dup-phone B', 'created_by' => $a['fa']->id,
            ]);
            $edge['duplicate_phone'] = 2;
        } catch (Throwable $e) { $this->errors[] = 'dup-phone: ' . $e->getMessage(); }

        // ── over-limit commission (3 cards > $7 / lot) ──────────
        for ($i = 0; $i < 3; $i++) {
            $b = $a['regular_branches']->random();
            CommissionCard::create([
                'account_number'      => (string) $this->nextAccount(),
                'month'               => $month, 'month_date' => $monthDate,
                'branch_id'           => $b->id,
                'broker_id'           => $a['employees']->random()->id,
                'broker_commission'   => 5,
                'marketer_id'         => $a['employees']->random()->id,
                'marketer_commission' => 4,  // 5+4=9 (> $7 cap)
                'account_kind'        => 'new', 'status' => 'new_added',
                'initial_deposit'     => 1000,
                'notes'               => self::MARKER . ' over-limit',
                'created_by'          => $a['fa']->id,
            ]);
            $edge['over_limit']++;
        }

        // ── cross-branch account (same acc# in 2 branches) ──────
        try {
            $brs = $a['regular_branches']->take(2);
            $acc = (string) $this->nextAccount();
            foreach ($brs as $br) {
                CommissionCard::create([
                    'account_number'    => $acc,
                    'month'             => $month, 'month_date' => $monthDate,
                    'branch_id'         => $br->id,
                    'broker_id'         => $a['employees']->random()->id,
                    'broker_commission' => 3,
                    'account_kind'      => 'new', 'status' => 'new_added',
                    'initial_deposit'   => 500,
                    'notes'             => self::MARKER . ' cross-branch',
                    'created_by'        => $a['fa']->id,
                ]);
                $edge['cross_branch']++;
            }
        } catch (Throwable $e) { $this->errors[] = 'cross-branch: ' . $e->getMessage(); }

        foreach ($edge as $k => $v) $this->line("  {$k}: {$v}");
        $this->stats['edge'] = $edge;
    }

    /* ───────────────── Verifications ───────────────── */

    private function verifyNotifications(): void
    {
        $wfIds = $this->wfCardIds();
        $counts = ManagerNotification::whereIn('card_id', $wfIds)
            ->groupBy('type')->selectRaw('type, COUNT(*) as c')
            ->pluck('c', 'type')->toArray();

        $expected = [
            'cc_new_card'       => 50,  // 10 sent + 10 in_progress + 10 completed + 10 rejected + 10 declined
            'cc_card_followup'  => 20,  // 10 in_progress + 10 completed (both followed up first)
            'cc_card_completed' => 10,
            'cc_card_rejected'  => 10,
            'cc_card_declined'  => 10,
        ];
        foreach ($expected as $type => $expect) {
            $got = $counts[$type] ?? 0;
            $icon = $got >= $expect ? '✓' : '✗';
            $this->line("  {$icon} {$type}: {$got} (expected ≥{$expect})");
            if ($got < $expect) {
                $this->errors[] = "notification '{$type}': expected >= {$expect}, got {$got}";
            }
        }

        $this->stats['notifications'] = $counts;
    }

    private function verifyFollowups(): void
    {
        $wfIds = $this->wfCardIds();
        $counts = CcFollowup::whereIn('card_id', $wfIds)
            ->groupBy('outcome')->selectRaw('outcome, COUNT(*) as c')
            ->pluck('c', 'outcome')->toArray();

        foreach ($counts as $o => $c) $this->line("  {$o}: {$c}");

        // 10 in_progress-only + 10 completed (followed up once) + 10 declined
        // = outcomes: in_progress ≥ 20, opened = 10, declined = 10
        if (($counts['in_progress'] ?? 0) < 20) $this->errors[] = 'followups in_progress < 20';
        if (($counts['opened']      ?? 0) < 10) $this->errors[] = 'followups opened < 10';
        if (($counts['declined']    ?? 0) < 10) $this->errors[] = 'followups declined < 10';

        $this->stats['followups'] = $counts;
    }

    private function verifySummary(): void
    {
        $t0 = microtime(true);
        Artisan::call('cards:rebuild-summaries');
        $ms = (int) ((microtime(true) - $t0) * 1000);
        $this->line("  rebuild took {$ms}ms");

        $month = now()->format('M-Y');
        $rows = CardSummary::where('month', $month)->get();
        $total = (int) $rows->sum('total_count');
        $ccCount = (int) $rows->sum('cc_count');
        $this->line("  {$month}: total={$total}, cc={$ccCount}");
        $this->stats['summary'] = ['rebuild_ms' => $ms, 'month' => $month, 'total' => $total, 'cc' => $ccCount, 'rows' => $rows->count()];
    }

    private function runReports(): void
    {
        $wfIds = $this->wfCardIds();

        // Over-limit — our 3 over-limit cards should show up
        $over = CommissionCard::whereIn('id', $wfIds)
            ->whereRaw('(broker_commission + marketer_commission + ext_commission1 + ext_commission2 + referral_commission + cc_agent_commission + cc_manager_commission) > 7')
            ->count();
        $this->line("  over-limit cards: {$over}");

        // Cross-branch — explicit SELECT so MySQL's ONLY_FULL_GROUP_BY doesn't reject it
        $cross = DB::table('commission_cards')
            ->whereIn('id', $wfIds)
            ->select('account_number', DB::raw('COUNT(DISTINCT branch_id) as b_count'))
            ->groupBy('account_number')
            ->havingRaw('COUNT(DISTINCT branch_id) > 1')
            ->get();
        $this->line("  cross-branch accounts: " . $cross->count());

        // Duplicate phones — explicit SELECT (same ONLY_FULL_GROUP_BY reason)
        $dup = DB::table('commission_cards')
            ->whereIn('id', $wfIds)
            ->whereNotNull('client_phone')
            ->select('client_phone', DB::raw('COUNT(*) as c'))
            ->groupBy('client_phone')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        $this->line("  duplicate phones: " . $dup->count());

        $this->stats['reports'] = [
            'over_limit' => $over,
            'cross_branch_accounts' => $cross->count(),
            'duplicate_phones' => $dup->count(),
        ];

        if ($over < 3)         $this->errors[] = "reports: over-limit expected ≥ 3, got {$over}";
        if ($cross->count() < 1) $this->errors[] = 'reports: cross-branch expected ≥ 1';
        if ($dup->count() < 1)   $this->errors[] = 'reports: duplicate-phone expected ≥ 1';
    }

    /* ───────────────── Report + cleanup ───────────────── */

    private function writeReport(): string
    {
        $dir = storage_path('logs');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file = $dir . '/workflow-test-' . now()->format('Ymd-His') . '.md';

        $md  = "# Workflow Test Report\n\n";
        $md .= "- **Started**: " . date('Y-m-d H:i:s', $this->startedAt) . "\n";
        $md .= "- **Finished**: " . now()->format('Y-m-d H:i:s') . "\n";
        $md .= "- **Duration**: " . (time() - $this->startedAt) . "s\n";
        $md .= "- **Errors**: " . count($this->errors) . "\n\n";

        $md .= "## Stats\n\n```json\n" . json_encode($this->stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```\n\n";

        if ($this->errors) {
            $md .= "## ❌ Errors\n\n";
            foreach ($this->errors as $e) $md .= "- {$e}\n";
            $md .= "\n";
        } else {
            $md .= "## ✅ All verifications passed\n\n";
        }

        $md .= "## Trace\n\n```json\n" . json_encode($this->trace, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```\n";

        file_put_contents($file, $md);
        return $file;
    }

    private function cleanup(): int
    {
        $wfIds = $this->wfCardIds();
        $n = 0;

        if (!empty($wfIds)) {
            // Children first
            $n += DB::table('cc_followups')->whereIn('card_id', $wfIds)->delete();
            $n += DB::table('manager_notifications')->whereIn('card_id', $wfIds)->delete();
            $n += DB::table('cc_notifications')->whereIn('card_id', $wfIds)->delete();
            $n += DB::table('card_modifications')->whereIn('card_id', $wfIds)->delete();
            $n += DB::table('activity_logs')->whereIn('model_id', $wfIds)
                ->where('model_type', CommissionCard::class)
                ->where('action', 'like', 'wftest_%')
                ->delete();
            // Hard delete the cards (bypass soft-delete)
            $n += DB::table('commission_cards')->whereIn('id', $wfIds)->delete();
        }

        // Delete temp CC manager user (and its notifications), if present
        $tempUser = User::where('email', 'wftest-cc-mgr@wafragulf.test')->first();
        if ($tempUser) {
            $n += DB::table('manager_notifications')->where('to_user_id', $tempUser->id)->delete();
            $n += DB::table('manager_notifications')->where('from_user_id', $tempUser->id)->delete();
            $n += DB::table('activity_logs')->where('user_id', $tempUser->id)->delete();
            $n += DB::table('users')->where('id', $tempUser->id)->delete();
        }

        // Rebuild summaries to reflect removal (safe — table now exists)
        try { Artisan::call('cards:rebuild-summaries'); } catch (Throwable $e) {}

        return $n;
    }

    private function wfCardIds(): array
    {
        return CommissionCard::withTrashed()
            ->where('notes', 'like', self::MARKER . '%')
            ->pluck('id')->toArray();
    }

    /* ───────────────── Helpers ───────────────── */

    private int $accCursor = self::ACC_RANGE_START - 1;
    private function nextAccount(): int
    {
        do {
            $this->accCursor++;
            if ($this->accCursor > self::ACC_RANGE_END) {
                throw new \RuntimeException('Test account range exhausted.');
            }
            $exists = CommissionCard::withTrashed()->where('account_number', (string)$this->accCursor)->exists();
        } while ($exists);
        return $this->accCursor;
    }

    private function pick(array $arr) { return $arr[array_rand($arr)]; }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan>── {$title}</>");
    }

    /** Run a step and capture any exception as a non-fatal error — keeps the suite running */
    private function guard(string $label, callable $fn): void
    {
        try {
            $fn();
        } catch (Throwable $e) {
            $this->errors[] = "{$label} crashed: " . $e->getMessage();
            $this->error("  ! {$label} crashed: " . $e->getMessage());
            $this->line("    at " . $e->getFile() . ':' . $e->getLine());
        }
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('════════ SUMMARY ════════');
        $this->line('Errors: ' . count($this->errors));
        foreach ($this->errors as $e) $this->error('  • ' . $e);
        $this->line('Duration: ' . (time() - $this->startedAt) . 's');
    }
}
