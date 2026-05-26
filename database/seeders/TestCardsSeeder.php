<?php
/**
 * TestCardsSeeder — 50 كرت تجريبي متنوع
 * Run: php artisan db:seed --class=TestCardsSeeder
 *   30 كرت عادي (branch-created, no CC fields)
 *   20 كرت CC  (cc_status = completed)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestCardsSeeder extends Seeder
{
    public function run(): void
    {
        $branches  = DB::table('branches')->pluck('id')->toArray();
        $employees = DB::table('employees')->where('status','approved')->pluck('id')->toArray();
        $adminUser = DB::table('users')->where('role','finance_admin')->value('id') ?? 1;

        if (empty($branches) || empty($employees)) {
            $this->command->warn('⚠️ لا توجد فروع أو موظفون معتمدون. أضفهم أولاً.');
            return;
        }

        $now  = now()->toDateTimeString();
        $used = [];

        // ══ 30 Regular Branch Cards (no cc_* fields) ══════════════
        $regular = [];
        for ($i = 0; $i < 30; $i++) {
            [$ac, $month] = $this->uniquePair($used, 700000, 709999);
            $broker   = $this->pick($employees);
            $marketer = rand(0,1) ? $this->pick($employees) : null;
            $bComm    = $this->pick([3, 4, 4.5, 5, 6, 7]);
            $mComm    = $marketer ? $this->pick([1, 1.5, 2, 2.5, 3]) : 0;
            $ext1     = (rand(0,3) === 0) ? $this->pick($employees) : null;
            $ext2     = ($ext1 && rand(0,2) === 0) ? $this->pick($employees) : null;

            $regular[] = [
                'account_number'      => (string)$ac,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $this->pick($branches),
                'broker_id'           => $broker,
                'broker_commission'   => $bComm,
                'marketer_id'         => $marketer,
                'marketer_commission' => $mComm,
                'ext_marketer1_id'    => $ext1,
                'ext_commission1'     => $ext1 ? $this->pick([0.5, 1, 1.5]) : 0,
                'ext_marketer2_id'    => $ext2,
                'ext_commission2'     => $ext2 ? $this->pick([0.5, 1]) : 0,
                'forex_commission'    => $this->pick([6, 7, 8, 9, 10]),
                'futures_commission'  => $this->pick([6, 7, 8]),
                'initial_deposit'     => $this->pick([500, 1000, 2000, 3000, 5000, 10000]),
                'monthly_deposit'     => $this->pick([0, 200, 500, 1000]),
                'account_kind'        => $this->pick(['new','new','new','sub']),
                'status'              => 'new_added',
                'notes'               => '🧪 تجريبي — Branch Card #' . ($i + 1),
                'created_by'          => $adminUser,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        // ══ 20 CC Cards (cc_status = completed) ═══════════════════
        $ccCards = [];
        for ($i = 0; $i < 20; $i++) {
            [$ac, $month] = $this->uniquePair($used, 800000, 809999);
            $ccBranch  = $this->pick($branches);
            $tgtBranch = $this->pickExcept($branches, $ccBranch);
            $ccAgent   = $this->pick($employees);
            $broker    = $this->pick($employees);
            $marketer  = rand(0,1) ? $this->pick($employees) : null;
            $bComm     = $this->pick([1.5, 2, 2.5, 3]);
            $mComm     = $marketer ? $this->pick([1, 1.5, 2]) : 0;

            $ccCards[] = [
                'account_number'      => (string)$ac,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $tgtBranch,
                'cc_branch_id'        => $ccBranch,
                'cc_agent_id'         => $ccAgent,
                'cc_agent_commission' => $this->pick([0.5, 1, 1.5]),
                'cc_status'           => 'completed',
                'broker_id'           => $broker,
                'broker_commission'   => $bComm,
                'marketer_id'         => $marketer,
                'marketer_commission' => $mComm,
                'ext_marketer1_id'    => null,
                'ext_commission1'     => 0,
                'ext_marketer2_id'    => null,
                'ext_commission2'     => 0,
                'forex_commission'    => $this->pick([6, 7, 8]),
                'futures_commission'  => $this->pick([6, 7, 8]),
                'initial_deposit'     => $this->pick([500, 1000, 2000, 3000]),
                'monthly_deposit'     => $this->pick([0, 300, 500]),
                'account_kind'        => $this->pick(['new','new','sub']),
                'status'              => 'new_added',
                'notes'               => '🧪 تجريبي — CC Card #' . ($i + 1),
                'created_by'          => $adminUser,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        // ── Insert in separate batches (different column sets) ─────
        foreach (array_chunk($regular, 10) as $chunk) {
            DB::table('commission_cards')->insert($chunk);
        }
        foreach (array_chunk($ccCards, 10) as $chunk) {
            DB::table('commission_cards')->insert($chunk);
        }

        $this->command->info('✅ تم إدراج ' . (count($regular) + count($ccCards)) . ' كرت تجريبي.');
        $this->command->info('   ' . count($regular) . ' كرت عادي + ' . count($ccCards) . ' كرت CC (مكتمل)');
    }

    // ── Helpers ────────────────────────────────────────────────────
    private function buildMonths(): array
    {
        $months = [];
        $d = Carbon::create(2025, 1, 1);
        $end = Carbon::create(2026, 8, 1);
        while ($d <= $end) {
            $months[] = ['label' => $d->format('M Y'), 'date' => $d->format('Y-m-d')];
            $d->addMonth();
        }
        return $months;
    }

    private function pick(array $arr): mixed
    {
        return $arr[array_rand($arr)];
    }

    private function pickExcept(array $arr, mixed $except): mixed
    {
        $f = array_values(array_filter($arr, fn($v) => $v !== $except));
        return !empty($f) ? $this->pick($f) : $this->pick($arr);
    }

    private function uniquePair(array &$used, int $min, int $max): array
    {
        $months = $this->buildMonths();
        do {
            $ac    = rand($min, $max);
            $month = $this->pick($months);
            $key   = $ac . '|' . $month['label'];
        } while (
            isset($used[$key]) ||
            DB::table('commission_cards')
              ->where('account_number', (string)$ac)
              ->where('month', $month['label'])
              ->exists()
        );
        $used[$key] = true;
        return [$ac, $month];
    }
}
