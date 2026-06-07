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
        Schema::create('winning_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Picks du coupon : [{match, league, prediction, odds, stars}, ...]
            $table->json('picks');

            // Métriques
            $table->decimal('total_odds',     6, 2);
            $table->unsignedTinyInteger('picks_count');
            $table->decimal('avg_confidence', 5, 2)->nullable();
            $table->unsignedTinyInteger('avg_stars')->nullable();

            // Mise et gain réels (saisis par l'utilisateur)
            $table->decimal('stake',       10, 2)->nullable();
            $table->decimal('actual_gain', 10, 2)->nullable();

            // Analyse Claude stockée pour personnalisation future
            $table->json('ai_analysis')->nullable();

            // Date de jeu (peut différer du created_at si saisie a posteriori)
            $table->date('played_at');

            $table->timestamps();
            $table->index(['user_id', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winning_coupons');
    }
};
