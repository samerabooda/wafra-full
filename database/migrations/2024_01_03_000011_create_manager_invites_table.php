<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manager_invites', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('role', ['branch_manager', 'viewer'])->default('branch_manager');
            $table->string('note', 255)->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->nullable(); // null = pending, filled = consumed
            $table->timestamps();
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_invites');
    }
};
