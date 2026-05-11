<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Competition;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competitions = [
            // 🔥 TENDANCES ACTUELLES
            [
                'sportradar_id' => 'sr:competition:270',
                'name' => 'CAN',
                'full_name' => 'Coupe d\'Afrique des Nations',
                'country' => 'Afrique',
                'icon' => '🏆',
                'priority' => 1,
                'is_active' => true,
                'is_trending' => true,
                'trending_start' => '2026-01-10',
                'trending_end' => '2026-02-10',
                'description' => 'Coupe d\'Afrique des Nations 2025',
            ],

            // TOP 5 CHAMPIONNATS
            [
                'sportradar_id' => 'sr:competition:17',
                'name' => 'Premier League',
                'full_name' => 'English Premier League',
                'country' => 'Angleterre',
                'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
                'priority' => 2,
                'is_active' => true,
                'is_trending' => true,
                'description' => 'Championnat d\'Angleterre',
            ],
            [
                'sportradar_id' => 'sr:competition:8',
                'name' => 'La Liga',
                'full_name' => 'La Liga Española',
                'country' => 'Espagne',
                'icon' => '🇪🇸',
                'priority' => 2,
                'is_active' => true,
                'is_trending' => true,
                'description' => 'Championnat d\'Espagne',
            ],
            [
                'sportradar_id' => 'sr:competition:35',
                'name' => 'Bundesliga',
                'full_name' => 'Deutsche Bundesliga',
                'country' => 'Allemagne',
                'icon' => '🇩🇪',
                'priority' => 2,
                'is_active' => true,
                'is_trending' => true,
                'description' => 'Championnat d\'Allemagne',
            ],
            [
                'sportradar_id' => 'sr:competition:23',
                'name' => 'Serie A',
                'full_name' => 'Serie A TIM',
                'country' => 'Italie',
                'icon' => '🇮🇹',
                'priority' => 2,
                'is_active' => true,
                'is_trending' => true,
                'description' => 'Championnat d\'Italie',
            ],
            [
                'sportradar_id' => 'sr:competition:34',
                'name' => 'Ligue 1',
                'full_name' => 'Ligue 1 Uber Eats',
                'country' => 'France',
                'icon' => '🇫🇷',
                'priority' => 2,
                'is_active' => true,
                'is_trending' => true,
                'description' => 'Championnat de France',
            ],

            // COUPES D'EUROPE
            [
                'sportradar_id' => 'sr:competition:7',
                'name' => 'Champions League',
                'full_name' => 'UEFA Champions League',
                'country' => 'Europe',
                'icon' => '⭐',
                'priority' => 3,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Ligue des Champions UEFA',
            ],
            [
                'sportradar_id' => 'sr:competition:679',
                'name' => 'Europa League',
                'full_name' => 'UEFA Europa League',
                'country' => 'Europe',
                'icon' => '🌍',
                'priority' => 3,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Ligue Europa UEFA',
            ],
            [
                'sportradar_id' => 'sr:competition:848',
                'name' => 'Conference League',
                'full_name' => 'UEFA Conference League',
                'country' => 'Europe',
                'icon' => '🏅',
                'priority' => 4,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Ligue de Conférence UEFA',
            ],

            // COUPES NATIONALES
            [
                'sportradar_id' => 'sr:competition:329',
                'name' => 'Copa del Rey',
                'full_name' => 'Copa del Rey',
                'country' => 'Espagne',
                'icon' => '🏆',
                'priority' => 5,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Coupe du Roi d\'Espagne',
            ],
            [
                'sportradar_id' => 'sr:competition:132',
                'name' => 'FA Cup',
                'full_name' => 'FA Cup',
                'country' => 'Angleterre',
                'icon' => '🏆',
                'priority' => 5,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Coupe d\'Angleterre',
            ],
            [
                'sportradar_id' => 'sr:competition:33',
                'name' => 'Coupe de France',
                'full_name' => 'Coupe de France',
                'country' => 'France',
                'icon' => '🏆',
                'priority' => 5,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Coupe de France',
            ],
            [
                'sportradar_id' => 'sr:competition:36193',
                'name' => 'EFL Cup',
                'full_name' => 'EFL Carabao Cup',
                'country' => 'Angleterre',
                'icon' => '🏆',
                'priority' => 6,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Coupe de la Ligue Anglaise',
            ],

            // AUTRES CHAMPIONNATS EUROPÉENS
            [
                'sportradar_id' => 'sr:competition:37',
                'name' => 'Eredivisie',
                'full_name' => 'Eredivisie',
                'country' => 'Pays-Bas',
                'icon' => '🇳🇱',
                'priority' => 7,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Championnat des Pays-Bas',
            ],
            [
                'sportradar_id' => 'sr:competition:238',
                'name' => 'Liga Portugal',
                'full_name' => 'Liga Portugal Bwin',
                'country' => 'Portugal',
                'icon' => '🇵🇹',
                'priority' => 7,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Championnat du Portugal',
            ],
            [
                'sportradar_id' => 'sr:competition:38',
                'name' => 'Pro League',
                'full_name' => 'Jupiler Pro League',
                'country' => 'Belgique',
                'icon' => '🇧🇪',
                'priority' => 7,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'Championnat de Belgique',
            ],

            // CHAMPIONNATS SECONDAIRES ANGLETERRE
            [
                'sportradar_id' => 'sr:competition:18',
                'name' => 'Championship',
                'full_name' => 'EFL Championship',
                'country' => 'Angleterre',
                'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
                'priority' => 8,
                'is_active' => true,
                'is_trending' => false,
                'description' => 'D2 Anglaise',
            ],

            // COMPÉTITIONS INTERNATIONALES
            [
                'sportradar_id' => 'sr:competition:1',
                'name' => 'Coupe du Monde',
                'full_name' => 'FIFA World Cup',
                'country' => 'International',
                'icon' => '🌍',
                'priority' => 1,
                'is_active' => false, // Pas en cours
                'is_trending' => false,
                'description' => 'Coupe du Monde FIFA',
            ],
            [
                'sportradar_id' => 'sr:competition:4',
                'name' => 'Euro',
                'full_name' => 'UEFA European Championship',
                'country' => 'Europe',
                'icon' => '🇪🇺',
                'priority' => 1,
                'is_active' => false, // Pas en cours
                'is_trending' => false,
                'description' => 'Championnat d\'Europe des Nations',
            ],
        ];

        foreach ($competitions as $data) {
            Competition::updateOrCreate(
                ['sportradar_id' => $data['sportradar_id']],
                $data
            );
        }

        $this->command->info('✅ ' . count($competitions) . ' compétitions créées/mises à jour');
    }
}

