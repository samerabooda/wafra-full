<?php

namespace App\Console\Commands;

use App\Models\{
    Branch, CommissionCard, Employee, User, CcNotification,
    CcFollowup, ManagerNotification, ActivityLog
};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Hash, Artisan};
use Throwable;

/**
 * Creates a batch of persistent CC cards for visual inspection in the Tracker,
 * Mailbox, and Call Center screens. UNLIKE test:system-workflow, this does NOT
 * clean up — the cards stay until you run `--cleanup`.
 *
 *   php artisan test:cc-batch                 # 80 cards, keep them
 *   php artisan test:cc-batch --count=120     # custom count, same spread
 *   php artisan test:cc-batch --cleanup       # remove all [CC-BATCH]-tagged cards
 *
 * Distribution (default 80):
 *   14 cc_pending (draft)
 *   14 branch_pending (sent → notification)
 *   14 in_progress (sent + followup)
 *   14 completed (full lifecycle, account opened)
 *   12 rejected (branch bounced)
 *   12 declined (customer refused)
 *
 * Marker: notes field starts with '[CC-BATCH]' — never collides with
 * test:system-workflow which uses '[WFTEST]'.
 */
class TestCcBatch extends Command
{
    protected $signature = 'test:cc-batch {--count=80 : Total cards to create} {--cleanup : Remove existing [CC-BATCH] cards instead}';
    protected $description = 'Create persistent CC test cards for Tracker/Inbox inspection';

    private const MARKER = '[CC-BATCH]';
    private const ACC_RANGE_START = 980000;
    private const ACC_RANGE_END   = 989999;

    private array $errors = [];
    private int $accCursor = self::ACC_RANGE_START - 1;

    public function handle(): int
    {
        if ($this->option('cleanup')) {
            $this->info('Cleanup: removing [CC-BATCH] records…');
            $n = $this->cleanup();
            $this->info("Removed {$n} records.");
            return 0;
        }

        $count = (int) $this->option('count');
        if ($count < 6) { $this->error('Count too small (min 6)'); return 1; }

        $this->info("Creating {$count} persistent CC cards…");

        try {
            $actors = $this->setupActors();
            if (!$actors) return 1;

            // Distribute: ~17% each of first 4, ~15% each of last 2
            $per = (int) floor($count / 6);
            $spread = [
                'cc_pending'     => $per,
                'branch_pending' => $per,
                'in_progress'    => $per,
                'completed'      => $per,
                'rejected'       => $per,
                'declined'       => $count - ($per * 5),
            ];

            foreach ($spread as $state => $n) {
                $this->line("  → {$state}: {$n}");
                for ($i = 0; $i < $n; $i++) {
                    try {
                        $this->createOne($actors, $state, $i + 1);
                    } catch (Throwable $e) {
                        $this->errors[] = "{$state} #{$i}: " . $e->getMessage();
                    }
                }
            }

            // Rebuild summaries once at the end
            try { Artisan::call('cards:rebuild-summaries'); } catch (Throwable $e) {}

            $created = CommissionCard::where('notes', 'like', self::MARKER . '%')->count();
            $this->info("✓ Created {$created} cards (errors: " . count($this->errors) . ')');
            if ($this->errors) {
                foreach (array_slice($this->errors, 0, 5) as $e) $this->warn('  ! ' . $e);
            }
            $this->info("Tagged with notes like '" . self::MARKER . " ...' — won't collide with other tests.");
            $this->info("Open the Tracker / Mailbox / Call Center screens to inspect.");
            $this->info("Remove later with: php artisan test:cc-batch --cleanup");
            return 0;

        } catch (Throwable $e) {
            $this->error('FATAL: ' . $e->getMessage());
            $this->error($e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }

    private function setupActors(): ?array
    {
        $fa = User::where('role', 'finance_admin')->where('is_active', true)->first();
        if (!$fa) { $this->error('No Finance Admin user found.'); return null; }

        $ccBranch = Branch::where('is_call_center', true)->where('is_active', true)->first();
        if (!$ccBranch) {
            $ccBranch = Branch::where('is_active', true)->first();
            $ccBranch->update(['is_call_center' => true]);
        }

        $regular = Branch::where('is_active', true)->where('id', '!=', $ccBranch->id)->get();
        if ($regular->isEmpty()) { $this->error('Need at least one non-CC active branch.'); return null; }

        $employees = Employee::where('status', 'approved')->get();
        if ($employees->isEmpty()) { $this->error('Need approved employees.'); return null; }

        // Reuse / create a temp CC manager so lifecycle notifications have a recipient
        $ccMgr = User::where('branch_id', $ccBranch->id)->where('role', 'branch_manager')->first();
        if (!$ccMgr) {
            $ccMgr = User::firstOrCreate(
                ['email' => 'wftest-cc-mgr@wafragulf.test'],
                [
                    'name'      => '[CC-BATCH] CC Manager',
                    'password'  => Hash::make('batch-' . bin2hex(random_bytes(8))),
                    'role'      => 'branch_manager',
                    'branch_id' => $ccBranch->id,
                    'is_active' => true,
                ]
            );
        }

        auth()->login($fa);

        return compact('fa', 'ccBranch', 'ccMgr', 'regular', 'employees');
    }

    private function createOne(array $a, string $state, int $i): void
    {
        $month     = now()->format('M-Y');
        $monthDate = now()->startOfMonth()->toDateString();
        $target    = $a['regular']->random();
        $phone     = '5' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);

        $card = CommissionCard::create([
            'account_number'        => (string) $this->nextAccount(),
            'month'                 => $month,
            'month_date'            => $monthDate,
            'branch_id'             => $target->id,
            'cc_branch_id'          => $a['ccBranch']->id,
            'cc_agent_id'           => $a['employees']->random()->id,
            'cc_agent_commission'   => $this->pick([0.5, 1, 1.5]),
            'cc_manager_commission' => 0.5,
            'cc_status'             => 'cc_pending',
            'status'                => 'new_added',
            'client_phone'          => $phone,
            'client_country_code'   => '+965',
            'client_source'         => 'cc-batch',
            'account_kind'          => 'new',
            'initial_deposit'       => 0,
            'monthly_deposit'       => 0,
            'notes'                 => self::MARKER . " {$state} #{$i}",
            'created_by'            => $a['ccMgr']->id,
        ]);

        // Walk the state transitions
        if ($state === 'cc_pending') { return; }

        $this->ccSend($card, $a['ccMgr']);

        if ($state === 'branch_pending') return;

        if (in_array($state, ['in_progress', 'completed'])) {
            $this->ccFollowup($card, $a['fa'], "اتصل بنا مرة أخرى بعد يومين — [CC-BATCH]");
        }
        if ($state === 'in_progress') return;

        if ($state === 'completed') {
            $this->ccComplete($card, $a['fa'], $a);
            return;
        }

        if ($state === 'rejected') {
            $this->ccReject($card, $a['fa'], "البيانات ناقصة — [CC-BATCH]");
            return;
        }

        if ($state === 'declined') {
            $this->ccDecline($card, $a['fa'], "العميل غيّر رأيه — [CC-BATCH]");
            return;
        }
    }

    /* ─── CC transitions (replicates CallCenterController) ─── */

    private function ccSend(CommissionCard $card, User $actor): void
    {
        $card->update(['cc_status' => 'branch_pending']);
        CcNotification::create([
            'card_id'=>$card->id,'from_branch_id'=>$card->cc_branch_id,'to_branch_id'=>$card->branch_id,
            'sent_by'=>$actor->id,'type'=>'card_sent','status'=>'unread',
            'message'=>self::MARKER . " عميل مهتم — هاتف {$card->client_phone}",
        ]);
        $this->fanNewCard($card, $actor);
        ActivityLog::record('ccbatch_sent', $card);
    }

    private function ccFollowup(CommissionCard $card, User $actor, string $note): void
    {
        $card->update(['cc_status' => 'in_progress']);
        CcFollowup::create([
            'card_id'=>$card->id,'user_id'=>$actor->id,'branch_id'=>$card->branch_id,
            'outcome'=>'in_progress','note'=>$note,
        ]);
        CcNotification::create([
            'card_id'=>$card->id,'from_branch_id'=>$card->branch_id,'to_branch_id'=>$card->cc_branch_id,
            'sent_by'=>$actor->id,'responded_by'=>$actor->id,'type'=>'card_followup','status'=>'unread',
            'message'=>self::MARKER . ' متابعة: ' . $note,
        ]);
        $this->fanLifecycle($card, $actor, 'cc_card_followup', 'متابعة جديدة', $note);
        ActivityLog::record('ccbatch_followup', $card, ['note'=>$note]);
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
            'card_id'=>$card->id,'user_id'=>$actor->id,'branch_id'=>$card->branch_id,
            'outcome'=>'opened','note'=>self::MARKER . " فتح الحساب #{$card->account_number}",
        ]);
        CcNotification::create([
            'card_id'=>$card->id,'from_branch_id'=>$card->branch_id,'to_branch_id'=>$card->cc_branch_id,
            'sent_by'=>$actor->id,'responded_by'=>$actor->id,'type'=>'card_completed','status'=>'unread',
            'message'=>self::MARKER . " تم فتح الحساب #{$card->account_number}",
        ]);
        $this->fanLifecycle($card, $actor, 'cc_card_completed', 'الحساب تم فتحه',
            "#{$card->account_number} إيداع {$card->initial_deposit}");
        ActivityLog::record('ccbatch_completed', $card);
    }

    private function ccReject(CommissionCard $card, User $actor, string $reason): void
    {
        $card->update(['cc_status'=>'rejected','cc_rejection_reason'=>$reason]);
        CcNotification::create([
            'card_id'=>$card->id,'from_branch_id'=>$card->branch_id,'to_branch_id'=>$card->cc_branch_id,
            'sent_by'=>$actor->id,'responded_by'=>$actor->id,'type'=>'card_rejected','status'=>'unread',
            'message'=>self::MARKER . ' رفض: ' . $reason,
        ]);
        $this->fanLifecycle($card, $actor, 'cc_card_rejected', 'الكرت رُفض', $reason);
        ActivityLog::record('ccbatch_rejected', $card, ['reason'=>$reason]);
    }

    private function ccDecline(CommissionCard $card, User $actor, string $reason): void
    {
        $card->update(['cc_status'=>'declined','cc_rejection_reason'=>$reason]);
        CcFollowup::create([
            'card_id'=>$card->id,'user_id'=>$actor->id,'branch_id'=>$card->branch_id,
            'outcome'=>'declined','note'=>$reason,
        ]);
        CcNotification::create([
            'card_id'=>$card->id,'from_branch_id'=>$card->branch_id,'to_branch_id'=>$card->cc_branch_id,
            'sent_by'=>$actor->id,'responded_by'=>$actor->id,'type'=>'card_declined','status'=>'unread',
            'message'=>self::MARKER . ' العميل رفض: ' . $reason,
        ]);
        $this->fanLifecycle($card, $actor, 'cc_card_declined', 'العميل رفض فتح الحساب', $reason);
        ActivityLog::record('ccbatch_declined', $card, ['reason'=>$reason]);
    }

    /* ─── Fan-out helpers (same logic as CallCenterController) ─── */

    private function fanNewCard(CommissionCard $card, User $actor): void
    {
        try {
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
            $phoneFull = trim(trim((string)$card->client_country_code) . ' ' . trim((string)$card->client_phone));
            ManagerNotification::fanOut($recipients, [
                'type'=>'cc_new_card','card_id'=>$card->id,
                'account_number'=>$card->account_number,'month'=>$card->month,
                'from_user_id'=>$actor->id,'from_branch_id'=>$card->cc_branch_id,
                'to_branch_id'=>$card->branch_id,
                'title'=>self::MARKER . ' عميل جديد',
                'body'=>"اختبار CC-BATCH — هاتف {$phoneFull}",
                'data'=>['batch'=>true,'phone'=>$phoneFull],
            ]);
        } catch (Throwable $e) {}
    }

    private function fanLifecycle(CommissionCard $card, User $actor, string $type, string $title, string $body): void
    {
        try {
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
                'type'=>$type,'card_id'=>$card->id,
                'account_number'=>$card->account_number,'month'=>$card->month,
                'from_user_id'=>$actor->id,'from_branch_id'=>$card->branch_id,
                'to_branch_id'=>$card->cc_branch_id,
                'title'=>self::MARKER . ' ' . $title,'body'=>$body,
                'data'=>['batch'=>true],
            ]);
        } catch (Throwable $e) {}
    }

    /* ─── Cleanup ─── */

    private function cleanup(): int
    {
        $ids = CommissionCard::withTrashed()
            ->where('notes', 'like', self::MARKER . '%')
            ->pluck('id')->toArray();

        $n = 0;
        if (!empty($ids)) {
            $n += DB::table('cc_followups')->whereIn('card_id', $ids)->delete();
            $n += DB::table('manager_notifications')->whereIn('card_id', $ids)->delete();
            $n += DB::table('cc_notifications')->whereIn('card_id', $ids)->delete();
            $n += DB::table('card_modifications')->whereIn('card_id', $ids)->delete();
            $n += DB::table('activity_logs')->whereIn('model_id', $ids)
                ->where('model_type', CommissionCard::class)
                ->where('action', 'like', 'ccbatch_%')
                ->delete();
            $n += DB::table('commission_cards')->whereIn('id', $ids)->delete();
        }

        // Also remove the temp CC manager if it exists
        $temp = User::where('email', 'wftest-cc-mgr@wafragulf.test')->first();
        if ($temp) {
            $n += DB::table('manager_notifications')->where('to_user_id', $temp->id)->delete();
            $n += DB::table('manager_notifications')->where('from_user_id', $temp->id)->delete();
            $n += DB::table('activity_logs')->where('user_id', $temp->id)->delete();
            $n += DB::table('users')->where('id', $temp->id)->delete();
        }

        try { Artisan::call('cards:rebuild-summaries'); } catch (Throwable $e) {}
        return $n;
    }

    /* ─── Helpers ─── */

    private function nextAccount(): int
    {
        do {
            $this->accCursor++;
            if ($this->accCursor > self::ACC_RANGE_END) {
                throw new \RuntimeException('CC-BATCH account range exhausted.');
            }
            $exists = CommissionCard::withTrashed()
                ->where('account_number', (string)$this->accCursor)->exists();
        } while ($exists);
        return $this->accCursor;
    }

    private function pick(array $arr) { return $arr[array_rand($arr)]; }
}
