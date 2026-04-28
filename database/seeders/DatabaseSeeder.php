<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Hash, DB};
use App\Models\{Branch, User, Employee, AccountType, AccountStatus,
                TradingType, CommissionCard, CardModification,
                CcNotification, Setting, ActivityLog};
use Carbon\Carbon;

/**
 * Wafra Gulf — Full Test Seeder
 * ─────────────────────────────
 * Generates ~5000 commission cards with realistic scenarios:
 *  • Multiple branches, managers, employees
 *  • Regular cards, CC cards (all statuses), modified cards
 *  • Permission boundary tests
 *  • Commission limit scenarios (under, at, over $8)
 *  • Sub-accounts (account_kind = sub)
 *  • External marketers, multiple commission splits
 *  • Soft-deleted cards (FA only)
 *  • Modification history
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->command->info('🌊 Wafra Gulf — Full Test Seeder (5000 cards)');

        // ── 1. LOOKUP TABLES ──────────────────────────────────
        $this->seedLookupTables();

        // ── 2. BRANCHES ───────────────────────────────────────
        $branches = $this->seedBranches();

        // ── 3. USERS (FA + Branch Managers) ───────────────────
        [$fa, $managers] = $this->seedUsers($branches);

        // ── 4. EMPLOYEES ──────────────────────────────────────
        $employees = $this->seedEmployees($branches, $fa);

        // ── 5. SETTINGS ───────────────────────────────────────
        $this->seedSettings();

        // ── 6. COMMISSION CARDS (5000) ────────────────────────
        $this->seedCards($branches, $fa, $managers, $employees);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->printSummary();
    }

    // ═══════════════════════════════════════════════════════
    private function seedLookupTables(): void
    {
        $this->command->line('  → Lookup tables...');
        foreach ([
            ['ECN','ECN',1], ['STP','STP',2], ['Cent','سنت',3],
            ['Swap-Free','بدون سواب',4],
        ] as [$en,$ar,$ord]) {
            AccountType::firstOrCreate(['name_en'=>$en], ['name_ar'=>$ar,'sort_order'=>$ord]);
        }
        foreach ([
            ['NEW','جديد',1], ['Sub','فرعي',2], ['Sub account','حساب فرعي',3],
            ['Transfer Broker','تحويل بروكر',4], ['IB account','حساب IB',5],
            ['Referral','إحالة',6],
        ] as [$en,$ar,$ord]) {
            AccountStatus::firstOrCreate(['name_en'=>$en], ['name_ar'=>$ar,'sort_order'=>$ord]);
        }
        foreach ([
            ['ECN','ECN',1], ['Forex','فوركس',2], ['Futures','عقود آجلة',3],
            ['Crypto','كريبتو',4],
        ] as [$en,$ar,$ord]) {
            TradingType::firstOrCreate(['name_en'=>$en], ['name_ar'=>$ar,'sort_order'=>$ord]);
        }
    }

    private function seedBranches(): array
    {
        $this->command->line('  → Branches...');
        $data = [
            ['HQ','المركز الرئيسي','Headquarters','Kuwait','Kuwait City'],
            ['CC','مركز الاتصال','Call Center','Kuwait','Kuwait City'],
            ['B01','بيروت','Beirut','Lebanon','Beirut'],
            ['B02','دمشق','Damascus','Syria','Damascus'],
            ['B03','القاهرة','Cairo','Egypt','Cairo'],
            ['B04','الرياض','Riyadh','KSA','Riyadh'],
            ['B05','دبي','Dubai','UAE','Dubai'],
            ['B06','عمّان','Amman','Jordan','Amman'],
            ['B07','الكويت','Kuwait','Kuwait','Kuwait City'],
            ['B08','بغداد','Baghdad','Iraq','Baghdad'],
        ];
        $out = [];
        foreach ($data as [$code,$ar,$en,$country,$city]) {
            $out[$code] = Branch::firstOrCreate(['code'=>$code],[
                'name_ar'=>$ar,'name_en'=>$en,'country'=>$country,'city'=>$city,
            ]);
        }
        return $out;
    }

    private function seedUsers(array $branches): array
    {
        $this->command->line('  → Users...');
        $fa = User::firstOrCreate(['email'=>'finance@wafragulf.com'], [
            'name'=>'محمد الشعلة','password'=>Hash::make('Wafra@2026!'),
            'role'=>'finance_admin','branch_id'=>$branches['HQ']->id,'is_active'=>true,
        ]);

        $managerData = [
            ['cc.manager@wafragulf.com',   'أحمد خالد',        'CC',  'Wafra@CC1!'],
            ['beirut@wafragulf.com',        'كريم السمراني',    'B01', 'Wafra@B01!'],
            ['damascus@wafragulf.com',      'ياسر الحلبي',      'B02', 'Wafra@B02!'],
            ['cairo@wafragulf.com',         'مصطفى حسن',        'B03', 'Wafra@B03!'],
            ['riyadh@wafragulf.com',        'عمر القحطاني',     'B04', 'Wafra@B04!'],
            ['dubai@wafragulf.com',         'خالد المنصوري',    'B05', 'Wafra@B05!'],
            ['amman@wafragulf.com',         'ليلى المصري',      'B06', 'Wafra@B06!'],
            ['kuwait@wafragulf.com',        'فيصل العنزي',      'B07', 'Wafra@B07!'],
            ['baghdad@wafragulf.com',       'حيدر الموسوي',     'B08', 'Wafra@B08!'],
        ];
        $managers = [];
        foreach ($managerData as [$email,$name,$code,$pass]) {
            $managers[$code] = User::firstOrCreate(['email'=>$email], [
                'name'=>$name,'password'=>Hash::make($pass),
                'role'=>'branch_manager','branch_id'=>$branches[$code]->id,'is_active'=>true,
            ]);
        }
        return [$fa, $managers];
    }

    private function seedEmployees(array $branches, User $fa): array
    {
        $this->command->line('  → Employees...');
        $data = [
            // [name, role, branch_code, broker_comm, mkt_comm, cc_comm]
            // ── HQ ──────────────────────────────────────────
            ['Samer Obeid',      'broker',   'HQ',  4.00, 3.00, 0.00],
            ['Samer Ali',        'broker',   'HQ',  4.00, 3.00, 0.00],
            // ── B01 Beirut ──────────────────────────────────
            ['Reyad Sabobah',    'broker',   'B01', 4.00, 3.00, 0.00],
            ['Reyad Khalil',     'broker',   'B01', 3.50, 2.50, 0.00],
            ['Nada Khalil',      'marketing', 'B01', 0.00, 3.00, 0.00],
            ['Sara Moussa',      'marketing', 'B01', 0.00, 2.50, 0.00],
            // ── B02 Damascus ────────────────────────────────
            ['Mohammad Khalil',  'broker',   'B02', 4.00, 3.00, 0.00],
            ['Ahmad Nassar',     'broker',   'B02', 4.00, 2.00, 0.00],
            ['Layla Darwish',    'marketing', 'B02', 0.00, 3.00, 0.00],
            // ── B03 Cairo ───────────────────────────────────
            ['M. Yehia',         'broker',   'B03', 4.00, 3.00, 0.00],
            ['Hassan Ibrahim',   'broker',   'B03', 3.50, 2.50, 0.00],
            ['Amira Said',       'marketing', 'B03', 0.00, 3.00, 0.00],
            // ── B04 Riyadh ──────────────────────────────────
            ['Fahad Bloshi',     'external', 'B04', 0.00, 2.00, 0.00],
            ['Khalid Alatawi',   'broker',   'B04', 4.00, 3.00, 0.00],
            ['Omar Alghamdi',    'marketing', 'B04', 0.00, 3.00, 0.00],
            // ── B05 Dubai ───────────────────────────────────
            ['Omar Nasser',      'broker',   'B05', 4.00, 3.00, 0.00],
            ['Maha Rashid',      'marketing', 'B05', 0.00, 3.00, 0.00],
            ['Ali Mansoor',      'broker',   'B05', 3.50, 2.00, 0.00],
            // ── B06 Amman ───────────────────────────────────
            ['Tarek Haddad',     'broker',   'B06', 4.00, 3.00, 0.00],
            ['Dana Khalaf',      'marketing', 'B06', 0.00, 2.50, 0.00],
            // ── B07 Kuwait ──────────────────────────────────
            ['Yousef Alrashidi', 'broker',   'B07', 4.00, 3.00, 0.00],
            ['Mona Alsabah',     'marketing', 'B07', 0.00, 3.00, 0.00],
            // ── B08 Baghdad ─────────────────────────────────
            ['Ali Hussain',      'broker',   'B08', 4.00, 2.50, 0.00],
            ['Zahraa Mahdi',     'marketing', 'B08', 0.00, 2.00, 0.00],
            // ── CC Agents ───────────────────────────────────
            ['علي العتيبي',     'cc_agent', 'CC',  0.00, 1.00, 1.00],
            ['سارة الزهراني',   'cc_agent', 'CC',  0.00, 1.00, 1.50],
            ['يوسف الحربي',     'cc_agent', 'CC',  0.00, 1.00, 0.75],
            ['منى العسيري',     'cc_agent', 'CC',  0.00, 1.00, 1.25],
            ['خالد الشمري',     'cc_agent', 'CC',  0.00, 1.00, 0.50],
        ];

        $emps = [];
        foreach ($data as [$name,$role,$code,$bc,$mc,$cc]) {
            $emp = Employee::firstOrCreate(
                ['name'=>$name,'branch_id'=>$branches[$code]->id],
                [
                    'role'=>$role,'broker_commission'=>$bc,
                    'marketing_commission'=>$mc,'cc_commission'=>$cc,
                    'status'=>'approved','is_base'=>true,'is_active'=>true,
                    'added_by'=>$fa->id,'approved_by'=>$fa->id,'approved_at'=>now(),
                ]
            );
            $emps[$name] = $emp;
        }
        return $emps;
    }

    private function seedSettings(): void
    {
        $this->command->line('  → Settings...');
        foreach ([
            ['commission_limit_enabled',  '1',    'boolean'],
            ['commission_limit_amount',   '8.00', 'decimal'],
            ['commission_warning_count',  '3',    'integer'],
        ] as [$key,$val,$type]) {
            Setting::updateOrCreate(['key'=>$key],['value'=>$val,'type'=>$type]);
        }
    }

    private function seedCards(array $branches, User $fa, array $managers, array $employees): void
    {
        $this->command->line('  → Generating 5000 test cards...');

        $acTypes    = AccountType::all()->keyBy('name_en');
        $acStatuses = AccountStatus::all()->keyBy('name_en');
        $tradTypes  = TradingType::all()->keyBy('name_en');

        // Branch employee pools
        $brokersByBranch  = [];
        $marketersByBranch = [];
        $ccAgents = [];

        foreach ($employees as $name => $emp) {
            if ($emp->role === 'broker' || $emp->role === 'external') {
                $brokersByBranch[$emp->branch_id][] = $emp;
            }
            if ($emp->role === 'marketing') {
                $marketersByBranch[$emp->branch_id][] = $emp;
            }
            if ($emp->role === 'cc_agent') {
                $ccAgents[] = $emp;
            }
        }

        $regularBranches = ['B01','B02','B03','B04','B05','B06','B07','B08'];
        $ccBranch = $branches['CC'];

        // ── Months pool (last 24 months) ──────────────────────
        $months = [];
        for ($i = 0; $i < 24; $i++) {
            $d = Carbon::now()->subMonths($i)->startOfMonth();
            $months[] = ['label' => $d->format('M Y'), 'date' => $d->toDateString()];
        }

        $bar = $this->command->getOutput()->createProgressBar(5000);
        $bar->start();

        $count = 0;
        $accountBase = 700000;

        // ══════════════════════════════════════════════════════
        // SCENARIO 1: Regular Cards — all 8 branches (3000 cards)
        // Various commission combinations, statuses, kinds
        // ══════════════════════════════════════════════════════
        $scenario1Count = 0;
        foreach ($regularBranches as $bCode) {
            $branch = $branches[$bCode];
            $brBrokers   = $brokersByBranch[$branch->id] ?? [];
            $brMarketers = $marketersByBranch[$branch->id] ?? [];
            $manager     = $managers[$bCode] ?? $fa;

            $cardsPerBranch = intval(3000 / count($regularBranches));

            for ($i = 0; $i < $cardsPerBranch; $i++) {
                $month      = $months[array_rand($months)];
                $broker     = $brBrokers ? $brBrokers[array_rand($brBrokers)] : null;
                $marketer   = $brMarketers && rand(0,2) > 0 ? $brMarketers[array_rand($brMarketers)] : null;
                $acType     = $acTypes->random();
                $acStatus   = $acStatuses->random();
                $tradType   = $tradTypes->random();
                $kind       = rand(0,4) === 0 ? 'sub' : 'new';

                // Commission scenarios
                $scenario = $i % 8;
                [$bc, $mc, $ec1, $ec2] = match($scenario) {
                    0 => [4.00, 3.00, 0.00, 0.00],  // Full: $7
                    1 => [4.00, 0.00, 0.00, 0.00],  // Broker only: $4
                    2 => [4.00, 3.00, 1.00, 0.00],  // With ext1: $8 (at limit)
                    3 => [3.00, 2.00, 1.00, 0.00],  // Varied: $6
                    4 => [5.00, 2.00, 0.00, 0.00],  // High broker: $7
                    5 => [4.00, 2.00, 1.00, 1.00],  // All 4: $8 (at limit)
                    6 => [4.00, 3.00, 0.00, 0.00],  // Standard: $7
                    7 => [2.00, 1.00, 0.00, 0.00],  // Low: $3
                };

                // Status: 80% active, 15% modified, 5% new_added
                $status = match(true) {
                    $i % 20 === 0  => 'new_added',
                    $i % 7 === 0   => 'modified',
                    default        => 'active',
                };

                // Deposits
                $initialDeposit  = rand(1, 200) * 500;
                $monthlyDeposit  = rand(1, 50) * 200;

                // Ext marketers (10% of cards)
                $extMkt1 = $ec1 > 0 && !empty($brMarketers) ? $brMarketers[0] : null;
                $extMkt2 = $ec2 > 0 && count($brMarketers) > 1 ? $brMarketers[1] : null;

                $acNum = (string)($accountBase + $count + 1);

                $card = CommissionCard::create([
                    'account_number'      => $acNum,
                    'month'               => $month['label'],
                    'month_date'          => $month['date'],
                    'branch_id'           => $branch->id,
                    'account_type_id'     => $acType->id,
                    'account_status_id'   => $acStatus->id,
                    'trading_type_id'     => $tradType->id,
                    'account_kind'        => $kind,
                    'broker_id'           => $broker?->id,
                    'broker_commission'   => $bc,
                    'marketer_id'         => $marketer?->id,
                    'marketer_commission' => $mc,
                    'ext_marketer1_id'    => $extMkt1?->id,
                    'ext_commission1'     => $ec1,
                    'ext_marketer2_id'    => $extMkt2?->id,
                    'ext_commission2'     => $ec2,
                    'initial_deposit'     => $initialDeposit,
                    'monthly_deposit'     => $monthlyDeposit,
                    'status'              => $status,
                    'created_by'          => $manager->id,
                ]);

                // Add modification history for modified cards
                if ($status === 'modified') {
                    CardModification::create([
                        'card_id'        => $card->id,
                        'account_number' => $card->account_number,
                        'month'          => $card->month,
                        'reason'         => ['تعديل العمولة', 'تحديث الإيداع', 'تصحيح البيانات', 'طلب العميل'][rand(0,3)],
                        'old_data'       => ['broker_commission'=>$bc+1.00,'initial_deposit'=>$initialDeposit-1000],
                        'new_data'       => ['broker_commission'=>$bc,'initial_deposit'=>$initialDeposit],
                        'modified_by'    => $manager->id,
                        'modified_at'    => now()->subDays(rand(1,30)),
                    ]);
                }

                $count++;
                $bar->advance();
            }
        }

        // ══════════════════════════════════════════════════════
        // SCENARIO 2: CC Cards — All 6 statuses (1200 cards)
        // ══════════════════════════════════════════════════════
        $ccStatuses = ['cc_pending','branch_pending','accepted','rejected','completed','completed'];
        // completed appears twice to make it 33% of CC cards

        for ($i = 0; $i < 1200; $i++) {
            $month   = $months[array_rand($months)];
            $bCode   = $regularBranches[array_rand($regularBranches)];
            $branch  = $branches[$bCode];
            $agent   = $ccAgents[array_rand($ccAgents)];
            $ccStat  = $ccStatuses[$i % count($ccStatuses)];
            $manager = $managers[$bCode] ?? $fa;

            $brBrokers   = $brokersByBranch[$branch->id] ?? [];
            $brMarketers = $marketersByBranch[$branch->id] ?? [];
            $broker      = $brBrokers ? $brBrokers[array_rand($brBrokers)] : null;
            $marketer    = $brMarketers && $ccStat === 'completed' && rand(0,1) ? $brMarketers[array_rand($brMarketers)] : null;

            $ccComm = $agent->cc_commission;

            // For completed cards - fill all commission data
            [$bc, $mc] = match(true) {
                $ccStat === 'completed' && $ccComm <= 1.00 => [4.00, 3.00],  // $8 total
                $ccStat === 'completed' && $ccComm <= 1.50 => [3.50, 3.00],  // $8 total
                $ccStat === 'completed' => [4.00, 2.50],
                default => [0.00, 0.00],
            };

            $acNum = (string)($accountBase + $count + 1);

            $card = CommissionCard::create([
                'account_number'      => 'CC-'.$acNum,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $branch->id,
                'cc_branch_id'        => $ccBranch->id,
                'cc_agent_id'         => $agent->id,
                'cc_agent_commission' => $ccComm,
                'cc_status'           => $ccStat,
                'cc_rejection_reason' => $ccStat === 'rejected'
                    ? ['العميل غير مهتم', 'بيانات غير صحيحة', 'العميل في فرع آخر', 'تكرار'][rand(0,3)]
                    : null,
                'account_type_id'     => AccountType::inRandomOrder()->first()?->id,
                'account_status_id'   => AccountStatus::inRandomOrder()->first()?->id,
                'trading_type_id'     => TradingType::inRandomOrder()->first()?->id,
                'account_kind'        => rand(0,4) === 0 ? 'sub' : 'new',
                'broker_id'           => $ccStat === 'completed' ? $broker?->id : null,
                'broker_commission'   => $bc,
                'marketer_id'         => $ccStat === 'completed' ? $marketer?->id : null,
                'marketer_commission' => $mc,
                'initial_deposit'     => $ccStat === 'completed' ? rand(2,100)*500 : 0,
                'monthly_deposit'     => $ccStat === 'completed' ? rand(1,30)*200 : 0,
                'status'              => 'new_added',
                'notes'               => 'CC Test Card — Status: '.$ccStat,
                'created_by'          => $fa->id,
            ]);

            // Notifications for completed/accepted/rejected
            if (in_array($ccStat, ['branch_pending','accepted','rejected','completed'])) {
                CcNotification::create([
                    'card_id'        => $card->id,
                    'from_branch_id' => $ccBranch->id,
                    'to_branch_id'   => $branch->id,
                    'sent_by'        => $fa->id,
                    'type'           => 'card_sent',
                    'status'         => in_array($ccStat,['branch_pending']) ? 'unread' : 'read',
                    'message'        => "حساب CC-{$acNum} أُرسِل لفرع {$branch->name_ar}",
                ]);
            }
            if (in_array($ccStat, ['accepted','completed'])) {
                CcNotification::create([
                    'card_id'        => $card->id,
                    'from_branch_id' => $branch->id,
                    'to_branch_id'   => $ccBranch->id,
                    'sent_by'        => $manager->id,
                    'type'           => 'card_accepted',
                    'status'         => 'read',
                    'message'        => "الفرع قَبِل CC-{$acNum}",
                ]);
            }
            if ($ccStat === 'rejected') {
                CcNotification::create([
                    'card_id'        => $card->id,
                    'from_branch_id' => $branch->id,
                    'to_branch_id'   => $ccBranch->id,
                    'sent_by'        => $manager->id,
                    'type'           => 'card_rejected',
                    'status'         => 'unread',
                    'message'        => "الفرع رَفَضَ CC-{$acNum} — السبب: {$card->cc_rejection_reason}",
                ]);
            }
            if ($ccStat === 'completed') {
                CcNotification::create([
                    'card_id'        => $card->id,
                    'from_branch_id' => $branch->id,
                    'to_branch_id'   => $ccBranch->id,
                    'sent_by'        => $manager->id,
                    'type'           => 'card_completed',
                    'status'         => rand(0,1) ? 'read' : 'unread',
                    'message'        => "تم إكمال CC-{$acNum} — إيداع: \${$card->initial_deposit}",
                ]);
            }

            $count++;
            $bar->advance();
        }

        // ══════════════════════════════════════════════════════
        // SCENARIO 3: Permission boundary test cards (300 cards)
        // Cards at exactly $8, over $8 (warning test), under $3
        // ══════════════════════════════════════════════════════
        $permScenarios = [
            // [bc, mc, ec1, label]
            [4.00, 4.00, 0.00, 'OVER_LIMIT_8'],   // $8 exactly — broker too high
            [5.00, 3.00, 0.00, 'OVER_LIMIT_9'],   // $8 — total $8 ok but broker>4
            [3.00, 3.00, 2.00, 'AT_LIMIT_8'],     // $8 exactly with ext
            [4.00, 3.00, 1.00, 'AT_LIMIT_8_EXT'], // $8 with ext marketer
            [2.00, 1.00, 0.00, 'LOW_3'],          // Only $3
            [4.00, 0.00, 0.00, 'BROKER_ONLY_4'],  // $4 — no marketer
            [0.00, 3.00, 0.00, 'MARKETER_ONLY'],  // Marketer only
            [6.00, 2.00, 0.00, 'HIGH_BROKER_8'],  // $8 — high broker
        ];

        for ($i = 0; $i < 300; $i++) {
            $sc = $permScenarios[$i % count($permScenarios)];
            $month = $months[$i % count($months)];
            $bCode = $regularBranches[$i % count($regularBranches)];
            $branch = $branches[$bCode];
            $manager = $managers[$bCode] ?? $fa;
            $brBrokers = $brokersByBranch[$branch->id] ?? [];
            $broker = $brBrokers ? $brBrokers[0] : null;
            $acNum = (string)($accountBase + $count + 1);

            CommissionCard::create([
                'account_number'      => 'PERM-'.$acNum,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $branch->id,
                'account_type_id'     => AccountType::first()?->id,
                'account_status_id'   => AccountStatus::first()?->id,
                'trading_type_id'     => TradingType::first()?->id,
                'account_kind'        => 'new',
                'broker_id'           => $broker?->id,
                'broker_commission'   => $sc[0],
                'marketer_commission' => $sc[1],
                'ext_commission1'     => $sc[2],
                'initial_deposit'     => rand(5,50)*1000,
                'monthly_deposit'     => rand(1,10)*1000,
                'status'              => 'active',
                'notes'               => 'PERMISSION_TEST: '.$sc[3].' — total $'.($sc[0]+$sc[1]+$sc[2]),
                'created_by'          => $manager->id,
            ]);
            $count++;
            $bar->advance();
        }

        // ══════════════════════════════════════════════════════
        // SCENARIO 4: Sub-accounts linked to main accounts (200)
        // ══════════════════════════════════════════════════════
        for ($i = 0; $i < 200; $i++) {
            $month = $months[$i % count($months)];
            $bCode = $regularBranches[$i % count($regularBranches)];
            $branch = $branches[$bCode];
            $manager = $managers[$bCode] ?? $fa;
            $brBrokers = $brokersByBranch[$branch->id] ?? [];
            $broker = $brBrokers ? $brBrokers[array_rand($brBrokers)] : null;
            $acNum = (string)($accountBase + $count + 1);

            CommissionCard::create([
                'account_number'      => 'SUB-'.$acNum,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $branch->id,
                'account_type_id'     => AccountType::inRandomOrder()->first()?->id,
                'account_status_id'   => AccountStatus::where('name_en','Sub account')->first()?->id,
                'trading_type_id'     => TradingType::inRandomOrder()->first()?->id,
                'account_kind'        => 'sub',
                'broker_id'           => $broker?->id,
                'broker_commission'   => 3.00,
                'marketer_commission' => 2.00,
                'initial_deposit'     => rand(1,20)*1000,
                'monthly_deposit'     => rand(1,5)*500,
                'status'              => 'active',
                'notes'               => 'Sub-account test',
                'created_by'          => $manager->id,
            ]);
            $count++;
            $bar->advance();
        }

        // ══════════════════════════════════════════════════════
        // SCENARIO 5: Soft-deleted cards (FA only visible) (100)
        // ══════════════════════════════════════════════════════
        for ($i = 0; $i < 100; $i++) {
            $month = $months[$i % count($months)];
            $bCode = $regularBranches[$i % count($regularBranches)];
            $branch = $branches[$bCode];
            $acNum = (string)($accountBase + $count + 1);

            $card = CommissionCard::create([
                'account_number'      => 'DEL-'.$acNum,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $branch->id,
                'broker_commission'   => 4.00,
                'marketer_commission' => 3.00,
                'initial_deposit'     => rand(1,10)*1000,
                'monthly_deposit'     => 500,
                'status'              => 'inactive',
                'notes'               => 'DELETED - FA only visible',
                'created_by'          => $fa->id,
            ]);
            $card->delete(); // SoftDelete — only FA can see with withTrashed()
            $count++;
            $bar->advance();
        }

        // ══════════════════════════════════════════════════════
        // SCENARIO 6: Multi-modification history cards (200)
        // ══════════════════════════════════════════════════════
        for ($i = 0; $i < 200; $i++) {
            $month = $months[$i % count($months)];
            $bCode = $regularBranches[$i % count($regularBranches)];
            $branch = $branches[$bCode];
            $manager = $managers[$bCode] ?? $fa;
            $brBrokers = $brokersByBranch[$branch->id] ?? [];
            $broker = $brBrokers ? $brBrokers[0] : null;
            $acNum = (string)($accountBase + $count + 1);

            $originalBc = 4.00;
            $originalDep = rand(5,50)*1000;
            $card = CommissionCard::create([
                'account_number'      => 'MOD-'.$acNum,
                'month'               => $month['label'],
                'month_date'          => $month['date'],
                'branch_id'           => $branch->id,
                'account_type_id'     => AccountType::first()?->id,
                'account_status_id'   => AccountStatus::first()?->id,
                'broker_id'           => $broker?->id,
                'broker_commission'   => $originalBc,
                'marketer_commission' => 3.00,
                'initial_deposit'     => $originalDep,
                'monthly_deposit'     => 2000,
                'status'              => 'modified',
                'created_by'          => $manager->id,
            ]);

            // Multiple modification records
            $numMods = rand(2, 5);
            $prevBc = $originalBc + 1.00;
            for ($m = 0; $m < $numMods; $m++) {
                $newBc = $originalBc + ($numMods - $m - 1) * 0.5;
                CardModification::create([
                    'card_id'        => $card->id,
                    'account_number' => $card->account_number,
                    'month'          => $card->month,
                    'reason'         => ['تعديل العمولة','تحديث الإيداع','طلب مدير الفرع','تصحيح خطأ','تغيير البروكر'][$m%5],
                    'old_data'       => ['broker_commission'=>round($prevBc,2),'initial_deposit'=>$originalDep-($m*500)],
                    'new_data'       => ['broker_commission'=>round($newBc,2),'initial_deposit'=>$originalDep],
                    'modified_by'    => $manager->id,
                    'modified_at'    => now()->subDays($numMods - $m),
                ]);
                $prevBc = $newBc;
            }
            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\n  ✅ Generated {$count} commission cards");
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('════════════════════════════════════════════════');
        $this->command->info('✅ WAFRA GULF DATABASE SEEDED SUCCESSFULLY');
        $this->command->info('════════════════════════════════════════════════');

        $total   = CommissionCard::count();
        $withCC  = CommissionCard::whereNotNull('cc_branch_id')->count();
        $deleted = CommissionCard::withTrashed()->count() - $total;
        $modified= CommissionCard::where('status','modified')->count();
        $ccStats = CommissionCard::whereNotNull('cc_branch_id')
                                 ->selectRaw('cc_status, COUNT(*) as cnt')
                                 ->groupBy('cc_status')->pluck('cnt','cc_status');

        $this->command->table(['Metric','Count'], [
            ['Total Cards',        number_format($total)],
            ['Regular Cards',      number_format($total - $withCC)],
            ['CC Cards',           number_format($withCC)],
            ['Modified Cards',     number_format($modified)],
            ['Soft-deleted',       number_format($deleted)],
            ['Branches',           Branch::count()],
            ['Employees',          \App\Models\Employee::count()],
            ['Users/Managers',     \App\Models\User::count()],
            ['CC Notifications',   CcNotification::count()],
            ['Modification Records',\App\Models\CardModification::count()],
        ]);

        $this->command->info('');
        $this->command->info('CC Card Statuses:');
        foreach ($ccStats as $status => $cnt) {
            $this->command->line("  {$status}: {$cnt}");
        }

        $this->command->info('');
        $this->command->info('🔑 LOGIN CREDENTIALS:');
        $this->command->table(['Role','Email','Password','Branch'], [
            ['Finance Admin',   'finance@wafragulf.com',    'Wafra@2026!', 'HQ'],
            ['CC Manager',      'cc.manager@wafragulf.com', 'Wafra@CC1!',  'CC'],
            ['Branch — Beirut', 'beirut@wafragulf.com',     'Wafra@B01!',  'B01'],
            ['Branch — Dubai',  'dubai@wafragulf.com',      'Wafra@B05!',  'B05'],
            ['Branch — Cairo',  'cairo@wafragulf.com',      'Wafra@B03!',  'B03'],
            ['Branch — Riyadh', 'riyadh@wafragulf.com',     'Wafra@B04!',  'B04'],
        ]);
    }
}
