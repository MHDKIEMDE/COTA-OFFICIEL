<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Données de configuration stables uniquement.
     * Prédictions, matchs, utilisateurs et abonnements sont créés en temps réel
     * via les APIs externes et l'interface d'administration.
     */
    public function run(): void
    {
        $this->call([
            AppConfigSeeder::class,
            CompetitionSeeder::class,
            BookmakerRegionSeeder::class,
            BookmakerPaymentMethodsSeeder::class,
            BookmakerBlogSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
