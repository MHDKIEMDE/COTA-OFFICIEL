<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookmakerBlogSeeder extends Seeder
{
    public function run(): void
    {
        $blogs = [
            [
                'bookmaker_slug' => '1xbet',
                'promo_code'     => 'CMD1122',
                'bonus_title'    => 'Bonus 100% jusqu\'à 130 000 FCFA',
                'bonus_description' => 'Inscris-toi sur 1xBet avec le code promo CMD1122 et double ton premier dépôt jusqu\'à 130 000 FCFA. Disponible via Wave, Orange Money, Free Money.',
                'steps' => [
                    ['title' => 'Télécharge l\'app 1xBet', 'description' => 'Disponible sur Android et iOS. Assure-toi d\'être au Sénégal, Côte d\'Ivoire, Burkina Faso, Mali ou Niger.'],
                    ['title' => 'Crée ton compte', 'description' => 'Clique sur "S\'inscrire" et saisis le code promo CMD1122 dans le champ prévu à cet effet.'],
                    ['title' => 'Effectue ton 1er dépôt', 'description' => 'Minimum 500 FCFA via Wave, Orange Money ou Free Money. Ton bonus sera crédité automatiquement.'],
                    ['title' => 'Commence à parier', 'description' => 'Utilise les pronostics COTA pour maximiser tes chances de gagner avec ton bonus.'],
                ],
                'cta_label' => 'S\'inscrire avec le code CMD1122',
            ],
            [
                'bookmaker_slug' => 'betwinner',
                'promo_code'     => 'COTAWIN',
                'bonus_title'    => 'Bonus 100% jusqu\'à 75 000 FCFA',
                'bonus_description' => 'Utilise le code COTAWIN sur Betwinner et bénéficie d\'un bonus de bienvenue de 100% sur ton premier dépôt, jusqu\'à 75 000 FCFA.',
                'steps' => [
                    ['title' => 'Télécharge Betwinner', 'description' => 'App disponible sur Android et iOS. Compatible Wave et Orange Money.'],
                    ['title' => 'Inscris-toi', 'description' => 'Saisis le code COTAWIN lors de l\'inscription pour activer ton bonus.'],
                    ['title' => 'Dépose et double', 'description' => 'Minimum 200 FCFA. Ton bonus est crédité automatiquement sur ton compte bonus.'],
                    ['title' => 'Mise et gagne', 'description' => 'Utilise le bonus sur les matchs recommandés par COTA pour maximiser tes gains.'],
                ],
                'cta_label' => 'Ouvrir un compte Betwinner',
            ],
            [
                'bookmaker_slug' => 'melbet',
                'promo_code'     => 'COTA100',
                'bonus_title'    => 'Bonus jusqu\'à 100 000 FCFA',
                'bonus_description' => 'Avec le code COTA100 sur Melbet, obtiens jusqu\'à 100 000 FCFA de bonus sur ton premier dépôt. Retrait possible via Mobile Money.',
                'steps' => [
                    ['title' => 'Télécharge Melbet', 'description' => 'Compatible Android et iOS. Large couverture en Afrique de l\'Ouest et Centrale.'],
                    ['title' => 'Crée ton compte', 'description' => 'Entre le code COTA100 dans le champ "Code promo" lors de l\'inscription.'],
                    ['title' => 'Premier dépôt', 'description' => 'Minimum 300 FCFA. Le bonus est ajouté automatiquement après confirmation du dépôt.'],
                    ['title' => 'Parie avec COTA', 'description' => 'Combine tes bonus avec nos pronostics COTA pour de meilleures chances de gain.'],
                ],
                'cta_label' => 'Créer un compte Melbet',
            ],
        ];

        foreach ($blogs as $blog) {
            $bookmaker = DB::table('bookmakers')->where('slug', $blog['bookmaker_slug'])->first();
            if (!$bookmaker) continue;

            DB::table('bookmaker_blogs')->updateOrInsert(
                ['bookmaker_id' => $bookmaker->id],
                [
                    'bookmaker_id'       => $bookmaker->id,
                    'promo_code'         => $blog['promo_code'],
                    'bonus_title'        => $blog['bonus_title'],
                    'bonus_description'  => $blog['bonus_description'],
                    'steps'              => json_encode($blog['steps']),
                    'cta_label'          => $blog['cta_label'],
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]
            );
        }
    }
}
