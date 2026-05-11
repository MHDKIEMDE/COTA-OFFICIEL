<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_code_uses', function (Blueprint $table) {
            $table->id();
            $table->string('promo_code', 50)->default('CMD1122');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('bookmaker', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->index('promo_code');
            $table->index('bookmaker');
            $table->index('used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_uses');
    }
};
