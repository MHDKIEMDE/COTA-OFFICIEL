<?php

namespace Database\Seeders;

use App\Models\Bookmaker;
use App\Models\BookmakerTip;
use Illuminate\Database\Seeder;

class BookmakerTipSeeder extends Seeder
{
    // Astuces génériques applicables à tous les bookmakers
    private const GENERIC_TIPS = [
        [
            'title' => 'Paris simples',
            'icon'  => '⚽',
            'tips'  => [
                'Commence par les favoris clairs — cotes entre 1.30 et 1.70.',
                'Ne mise jamais plus de 5 % de ta bankroll sur un seul match.',
                'Analyse le classement, les blessures et les confrontations directes.',
                'Les matchs en milieu de semaine sont souvent moins prévisibles.',
            ],
            'sort_order' => 1,
        ],
        [
            'title' => 'Paris combinés',
            'icon'  => '🔗',
            'tips'  => [
                'Maximum 4–5 sélections pour garder un taux de réussite acceptable.',
                'Privilégie les Over/Under plutôt que les scores exacts.',
                'Utilise le coupon COTA comme base de combiné.',
                'Évite de combiner deux matchs de la même équipe dans un ticket.',
            ],
            'sort_order' => 2,
        ],
        [
            'title' => 'Paris Live',
            'icon'  => '🔴',
            'tips'  => [
                'Attends les 10 premières minutes pour observer le rythme du match.',
                'Un but encaissé tôt fait monter la cote du favori — opportunité !',
                'Le Cash Out est ton meilleur ami — sécurise tes gains.',
                'Surveille les stats de possession et tirs pour anticiper.',
            ],
            'sort_order' => 3,
        ],
        [
            'title' => 'Gestion de bankroll',
            'icon'  => '💰',
            'tips'  => [
                'Fixe une limite de perte journalière et respecte-la.',
                'Ne cours jamais après tes pertes — discipline avant tout.',
                'Tiens un journal de tes paris pour analyser tes résultats.',
                'Les prédictions COTA 4 étoiles ont le meilleur ROI historique.',
            ],
            'sort_order' => 4,
        ],
    ];

    public function run(): void
    {
        $bookmakers = Bookmaker::all();

        if ($bookmakers->isEmpty()) {
            $this->command->warn('Aucun bookmaker trouvé — lance BookmakerRegionSeeder d\'abord.');
            return;
        }

        foreach ($bookmakers as $bm) {
            foreach (self::GENERIC_TIPS as $tipData) {
                BookmakerTip::firstOrCreate(
                    [
                        'bookmaker_id' => $bm->id,
                        'title'        => $tipData['title'],
                    ],
                    [
                        'icon'       => $tipData['icon'],
                        'tips'       => $tipData['tips'],
                        'sort_order' => $tipData['sort_order'],
                        'is_active'  => true,
                    ]
                );
            }
        }

        $count = $bookmakers->count() * count(self::GENERIC_TIPS);
        $this->command->info("BookmakerTipSeeder : {$count} astuces créées.");
    }
}
