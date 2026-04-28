<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('permission', 50)
                  ->comment('dashboard|cards|modified|reports|create_card|edit_card|employees|import|export|branch_switch');
            $table->boolean('granted')->default(true);
            $table->timestamps();
            $table->unique(['user_id','permission'], 'uq_user_perm');
        });
    }
    public function down(): void { Schema::dropIfExists('user_permissions'); }
};
