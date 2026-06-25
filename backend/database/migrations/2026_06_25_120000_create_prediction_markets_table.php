<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->constrained()->cascadeOnDelete();

            // Famille de marché pour le switch UI : resultat / buts / equipe / tirs / corners / cartons / score
            $table->string('category', 30);

            // Type de pari (1X2, Over/Under, BTTS, Team Goals, Corners, Cards, Shots, Score Exact, Double Chance)
            $table->string('bet_type', 30);
            // Sélection lisible (ex: "Over 2.5", "Oui", "PSG Over 1.5")
            $table->string('outcome', 60);
            // Code universel (ex: "1", "X2", "O2.5", "GG", "COR O8.5")
            $table->string('market_selection', 30)->nullable();

            $table->decimal('odds', 6, 2);
            $table->decimal('market_score', 5, 2);       // confiance 0–100 du marché
            $table->enum('score_tier', ['gold', 'standard', 'bronze'])->nullable();
            $table->enum('active_side', ['home', 'away', 'both', 'none'])->default('none');
            $table->string('engine', 20)->nullable();    // force / goals / team_goals / corners / cards / shots / underdog / high_variance

            $table->boolean('is_primary')->default(false);  // marché principal affiché par défaut
            $table->boolean('is_premium')->default(false);  // alternatif réservé premium (ouvert en local)
            $table->boolean('is_risky')->default(false);    // pari tenté sur l'équipe faible

            // Résultat par marché (suivi indépendant du principal)
            $table->enum('status', ['pending', 'won', 'lost', 'void'])->default('pending');

            $table->timestamps();

            $table->index(['prediction_id', 'category'], 'idx_pm_prediction_category');
            $table->index('is_primary', 'idx_pm_is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_markets');
    }
};
