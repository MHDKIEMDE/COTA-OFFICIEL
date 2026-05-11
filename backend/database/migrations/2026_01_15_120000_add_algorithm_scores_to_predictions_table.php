<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les colonnes manquantes pour l'algorithme 9 criteres
     */
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            // Nouveaux criteres de l'algorithme v3.0
            $table->decimal('score_weather', 5, 2)->default(0)->after('score_time');
            $table->decimal('score_shots', 5, 2)->default(0)->after('score_weather');
            $table->decimal('score_physical', 5, 2)->default(0)->after('score_shots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['score_weather', 'score_shots', 'score_physical']);
        });
    }
};
