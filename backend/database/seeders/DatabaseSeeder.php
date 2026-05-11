<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * NOTE: Les seeders sont désactivés pour utiliser uniquement les vraies données
     * générées par les jobs automatisés (GenerateAllPredictionsJob).
     * Les données de test doivent être créées via l'API ou l'interface d'administration.
     */
    public function run(): void
    {
        // Les seeders sont désactivés - utiliser uniquement les vraies données de l'API
        // Les pronostics sont générés automatiquement par GenerateAllPredictionsJob
    }
}
