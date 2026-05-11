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
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();

            // Période
            $table->date('date'); // Date statistiques (quotidiennes)
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('daily');

            // Statistiques globales
            $table->integer('total_predictions')->default(0);
            $table->integer('predictions_won')->default(0);
            $table->integer('predictions_lost')->default(0);
            $table->integer('predictions_void')->default(0);
            $table->decimal('win_rate', 5, 2)->default(0); // Pourcentage (ex: 65.50)
            $table->decimal('roi', 8, 2)->default(0); // Return On Investment

            // Par compétition (JSON)
            $table->text('stats_by_competition')->nullable(); // {"Premier League": {"won": 5, "lost": 2, "win_rate": 71.4}, ...}

            // Par type de pari (JSON)
            $table->text('stats_by_bet_type')->nullable(); // {"1X2": {"won": 10, "lost": 3}, "Over/Under": {...}, ...}

            // Cote moyenne
            $table->decimal('average_odds', 5, 2)->default(0);
            $table->decimal('highest_odds', 5, 2)->default(0);

            // Algorithme
            $table->decimal('avg_algorithm_score', 5, 2)->default(0); // Score moyen des pronostics

            // Métadonnées
            $table->timestamps();

            // Index
            $table->unique(['date', 'period_type']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
