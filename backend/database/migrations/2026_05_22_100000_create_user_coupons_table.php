<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();

            // Picks sélectionnés par l'utilisateur depuis les prédictions du jour
            // [{prediction_id, match, league, prediction, odds, stars, confidence}, ...]
            $table->json('picks');

            $table->decimal('total_odds',     6, 2);
            $table->unsignedTinyInteger('picks_count');

            // Mise de l'utilisateur (optionnel)
            $table->decimal('stake', 10, 2)->nullable();

            // Résultat : pending | won | lost | partial
            $table->enum('status', ['pending', 'won', 'lost', 'partial'])->default('pending');

            // Gain réel (renseigné par l'utilisateur après le résultat)
            $table->decimal('actual_gain', 10, 2)->nullable();

            // Date de jeu
            $table->date('played_at');

            $table->timestamps();
            $table->index(['user_id', 'played_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_coupons');
    }
};
