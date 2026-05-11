<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * NOTE: Ce seeder est désactivé. Les matchs sont créés automatiquement
     * par le job GenerateAllPredictionsJob à partir des vraies données de l'API.
     */
    public function run(): void
    {
        // Seeder désactivé - utiliser uniquement les vraies données de l'API
    }
}
