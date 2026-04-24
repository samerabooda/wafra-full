<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('HQ, B01, B02...');
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->string('country', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });
    }
    public function down(): void { Schema::dropIfExists('branches'); }
};
