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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            // IDs de l'API-Football
            $table->bigInteger('match_id')->unique()->comment('ID du match depuis API-Football');
            $table->bigInteger('home_team_id')->comment('ID équipe domicile API-Football');
            $table->bigInteger('away_team_id')->comment('ID équipe extérieur API-Football');
            $table->bigInteger('competition_id')->comment('ID compétition API-Football');

            // Informations du match
            $table->string('home_team');
            $table->string('away_team');
            $table->string('competition');
            $table->string('country')->nullable();
            $table->string('competition_logo')->nullable();

            // Date et heure
            $table->dateTime('match_date');
            $table->string('match_time', 10)->nullable();
            $table->string('timezone')->default('Africa/Ouagadougou');

            // Scores
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->integer('home_score_halftime')->nullable();
            $table->integer('away_score_halftime')->nullable();

            // Statut du match
            $table->enum('status', [
                'scheduled',    // Programmé
                'live',         // En cours
                'halftime',     // Mi-temps
                'finished',     // Terminé
                'postponed',    // Reporté
                'cancelled',    // Annulé
                'abandoned',    // Abandonné
            ])->default('scheduled');

            $table->string('status_long')->nullable()->comment('Statut détaillé depuis API');
            $table->integer('elapsed_time')->nullable()->comment('Temps écoulé en minutes');

            // Statistiques pré-match (pour l'algorithme)
            $table->json('home_team_form')->nullable()->comment('Forme récente équipe domicile');
            $table->json('away_team_form')->nullable()->comment('Forme récente équipe extérieur');
            $table->json('h2h_history')->nullable()->comment('Historique confrontations');
            $table->json('standings_info')->nullable()->comment('Classement des équipes');

            // Statistiques du match (live/post-match)
            $table->json('match_statistics')->nullable()->comment('Stats détaillées du match');

            // Métadonnées
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('referee')->nullable();

            // Gestion de cache
            $table->timestamp('last_api_fetch')->nullable()->comment('Dernière récupération depuis API');

            $table->timestamps();

            // Index pour performances
            $table->index('match_date');
            $table->index('status');
            $table->index('competition_id');
            $table->index(['home_team_id', 'away_team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
