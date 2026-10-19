<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Relax the employees.role ENUM to a VARCHAR so new roles (e.g. broker_marketer)
// can be added without future enum migrations.
return new class extends Migration
{
    public function up(): void
    {
        try { DB::statement("ALTER TABLE employees MODIFY role VARCHAR(30) NOT NULL DEFAULT 'broker'"); }
        catch (\Throwable $e) { /* already varchar — ignore */ }
    }

    public function down(): void
    {
        try { DB::statement("ALTER TABLE employees MODIFY role ENUM('broker','marketing','external','other') NOT NULL DEFAULT 'broker'"); }
        catch (\Throwable $e) {}
    }
};
