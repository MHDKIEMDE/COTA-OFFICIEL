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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade'); // Parrain
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade'); // Filleul

            // Statut
            $table->enum('status', ['pending', 'validated', 'rewarded'])->default('pending');
            $table->timestamp('validated_at')->nullable(); // Date validation (filleul actif)

            // Récompenses
            $table->integer('reward_days')->default(0); // Jours premium gagnés (3, 7, 30, ou 365)
            $table->enum('reward_tier', ['first', 'tier_3', 'tier_5', 'tier_10'])->nullable(); // 1er, 3e, 5e, 10e filleul
            $table->boolean('reward_applied')->default(false);
            $table->timestamp('reward_applied_at')->nullable();

            // Métadonnées
            $table->string('referral_source')->nullable(); // Ex: "whatsapp", "facebook", "sms"
            $table->timestamps();

            // Index
            $table->index('referrer_id');
            $table->index('referred_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
