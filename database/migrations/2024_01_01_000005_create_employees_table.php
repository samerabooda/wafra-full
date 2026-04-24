<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->nullable()->unique();
            $table->enum('role', ['broker','marketing','external','other'])->default('broker');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->decimal('broker_commission', 8, 2)->default(4.00)->comment('$/lot');
            $table->decimal('marketing_commission', 8, 2)->default(3.00)->comment('$/lot');
            $table->enum('status', ['pending','approved','rejected'])->default('approved');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->boolean('is_base')->default(false)->comment('Cannot be deleted');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('role');
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
