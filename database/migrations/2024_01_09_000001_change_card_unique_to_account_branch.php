<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // The old global (account_number, month) unique blocked the same account number
        // from existing in two different branches. Each branch keeps its own card, so the
        // uniqueness is now (account_number, branch_id) — one card per account PER branch.
        $this->dropIndexIfExists('uq_ac_month');
        if (!$this->indexExists('uq_ac_branch')) {
            DB::statement('ALTER TABLE `commission_cards` ADD UNIQUE INDEX `uq_ac_branch` (`account_number`, `branch_id`)');
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('uq_ac_branch');
        if (!$this->indexExists('uq_ac_month')) {
            DB::statement('ALTER TABLE `commission_cards` ADD UNIQUE INDEX `uq_ac_month` (`account_number`, `month`)');
        }
    }

    private function indexExists(string $name): bool
    {
        return count(DB::select("SHOW INDEX FROM `commission_cards` WHERE Key_name = ?", [$name])) > 0;
    }
    private function dropIndexIfExists(string $name): void
    {
        if ($this->indexExists($name)) {
            DB::statement("ALTER TABLE `commission_cards` DROP INDEX `{$name}`");
        }
    }
};
