<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('card_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('commission_cards')->cascadeOnDelete();
            $table->string('account_number', 30);
            $table->string('month', 20);
            $table->string('reason', 200);
            $table->text('notes')->nullable();
            $table->json('old_data')->comment('Previous field values');
            $table->json('new_data')->comment('New field values');
            $table->foreignId('modified_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('modified_at')->useCurrent();
            $table->index('card_id');
            $table->index('account_number');
            $table->index('modified_at');
        });
    }
    public function down(): void { Schema::dropIfExists('card_modifications'); }
};
