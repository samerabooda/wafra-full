<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Branch, User, Employee, AccountType, AccountStatus, TradingType};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌊 Seeding Wafra Gulf database...');

        // ── 1. Branches ───────────────────────────────────────
        $this->command->line('  → Branches...');
        $branchesData = [
            ['code'=>'HQ',  'name_ar'=>'المركز الرئيسي', 'name_en'=>'Headquarters', 'country'=>'Kuwait',  'city'=>'Kuwait City'],
            ['code'=>'CC',  'name_ar'=>'مركز الاتصال',      'name_en'=>'Call Center', 'country'=>'Kuwait',  'city'=>'Kuwait City'],
            ['code'=>'B01', 'name_ar'=>'بيروت',           'name_en'=>'Beirut',        'country'=>'Lebanon', 'city'=>'Beirut'],
            ['code'=>'B02', 'name_ar'=>'دمشق',            'name_en'=>'Damascus',      'country'=>'Syria',   'city'=>'Damascus'],
            ['code'=>'B03', 'name_ar'=>'القاهرة',         'name_en'=>'Cairo',         'country'=>'Egypt',   'city'=>'Cairo'],
            ['code'=>'B04', 'name_ar'=>'الرياض',          'name_en'=>'Riyadh',        'country'=>'KSA',     'city'=>'Riyadh'],
            ['code'=>'B05', 'name_ar'=>'دبي',             'name_en'=>'Dubai',         'country'=>'UAE',     'city'=>'Dubai'],
            ['code'=>'B06', 'name_ar'=>'عمّان',           'name_en'=>'Amman',         'country'=>'Jordan',  'city'=>'Amman'],
            ['code'=>'B07', 'name_ar'=>'الكويت',          'name_en'=>'Kuwait',        'country'=>'Kuwait',  'city'=>'Kuwait City'],
            ['code'=>'B08', 'name_ar'=>'بغداد',           'name_en'=>'Baghdad',       'country'=>'Iraq',    'city'=>'Baghdad'],
        ];

        $branches = [];
        foreach ($branchesData as $b) {
            $branches[$b['code']] = Branch::firstOrCreate(['code' => $b['code']], $b);
        }

        // ── 2. Finance Admin user ─────────────────────────────
        $this->command->line('  → Finance Admin...');
        $fa = User::firstOrCreate(
            ['email' => 'finance@wafragulf.com'],
            [
                'name'      => 'محمد الشعلة',
                'password'  => Hash::make('Wafra@2026!'),
                'role'      => 'finance_admin',
                'branch_id' => $branches['HQ']->id,
                'is_active' => true,
            ]
        );

        // ── 3. Employees ──────────────────────────────────────
        $this->command->line('  → Employees...');
        $employeesData = [
            ['name'=>'Samer Obeid',   'role'=>'broker',   'code'=>'HQ',  'bc'=>4.00, 'mc'=>3.00],
            ['name'=>'Samer',         'role'=>'broker',   'code'=>'HQ',  'bc'=>4.00, 'mc'=>3.00],
            ['name'=>'Reyad Sabobah', 'role'=>'broker',   'code'=>'B01', 'bc'=>4.00, 'mc'=>3.00],
            ['name'=>'Reyad',         'role'=>'broker',   'code'=>'B01', 'bc'=>4.00, 'mc'=>3.00],
            ['name'=>'Mohammad',      'role'=>'broker',   'code'=>'B02', 'bc'=>4.00, 'mc'=>3.00],
            ['name'=>'M. Yehia',      'role'=>'broker',   'code'=>'B03', 'bc'=>4.00, 'mc'=>3.00],
            ['name'=>'Fahad Bloshi',  'role'=>'external', 'code'=>'B04', 'bc'=>0.00, 'mc'=>2.00],
        ];

        foreach ($employeesData as $e) {
            Employee::firstOrCreate(
                ['name' => $e['name']],
                [
                    'role'                 => $e['role'],
                    'branch_id'            => $branches[$e['code']]->id,
                    'broker_commission'    => $e['bc'],
                    'marketing_commission' => $e['mc'],
                    'status'               => 'approved',
                    'is_base'              => true,
                    'is_active'            => true,
                    'added_by'             => $fa->id,
                    'approved_by'          => $fa->id,
                    'approved_at'          => now(),
                ]
            );
        }

        // ── 4. Account Types ──────────────────────────────────
        $this->command->line('  → Account Types...');
        $accountTypes = [
            ['name_en'=>'ECN',  'name_ar'=>'ECN',  'sort_order'=>1],
            ['name_en'=>'STP',  'name_ar'=>'STP',  'sort_order'=>2],
            ['name_en'=>'Cent', 'name_ar'=>'سنت', 'sort_order'=>3],
        ];
        foreach ($accountTypes as $t) {
            AccountType::firstOrCreate(['name_en' => $t['name_en']], $t);
        }

        // ── 5. Account Statuses ───────────────────────────────
        $this->command->line('  → Account Statuses...');
        $statuses = [
            ['name_en'=>'NEW',             'name_ar'=>'جديد',         'sort_order'=>1],
            ['name_en'=>'Sub',             'name_ar'=>'فرعي',         'sort_order'=>2],
            ['name_en'=>'Sub account',     'name_ar'=>'حساب فرعي',   'sort_order'=>3],
            ['name_en'=>'Transfer Broker', 'name_ar'=>'تحويل بروكر', 'sort_order'=>4],
            ['name_en'=>'IB account',      'name_ar'=>'حساب IB',     'sort_order'=>5],
        ];
        foreach ($statuses as $s) {
            AccountStatus::firstOrCreate(['name_en' => $s['name_en']], $s);
        }

        // ── 6. Trading Types ──────────────────────────────────
        $this->command->line('  → Trading Types...');
        $tradingTypes = [
            ['name_en'=>'ECN',     'name_ar'=>'ECN',          'sort_order'=>1],
            ['name_en'=>'Forex',   'name_ar'=>'فوركس',        'sort_order'=>2],
            ['name_en'=>'Futures', 'name_ar'=>'عقود آجلة',   'sort_order'=>3],
        ];
        foreach ($tradingTypes as $t) {
            TradingType::firstOrCreate(['name_en' => $t['name_en']], $t);
        }

        $this->command->info('');
        $this->command->info('✅ Wafra Gulf database seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Field', 'Value'],
            [
                ['Finance Admin Email',    'finance@wafragulf.com'],
                ['Finance Admin Password', 'Wafra@2026!'],
                ['Branches',               count($branchesData).' branches'],
                ['Employees',              count($employeesData).' base employees'],
                ['Account Types',          count($accountTypes)],
                ['Account Statuses',       count($statuses)],
                ['Trading Types',          count($tradingTypes)],
            ]
        );

        // ── Default settings for commission limit ──────────────
        $settingsData = [
            ['key'=>'commission_limit_enabled',   'value'=>'1',    'type'=>'boolean', 'description'=>'تفعيل/إيقاف حد إجمالي العمولات'],
            ['key'=>'commission_limit_amount',    'value'=>'8.00', 'type'=>'decimal', 'description'=>'الحد الأقصى لإجمالي العمولات ($/lot)'],
            ['key'=>'commission_warning_count',   'value'=>'3',    'type'=>'integer', 'description'=>'عدد التحذيرات قبل الحجب'],
        ];
        foreach ($settingsData as $s) {
            \App\Models\Setting::updateOrCreate(['key'=>$s['key']], $s);
        }
    }
}
