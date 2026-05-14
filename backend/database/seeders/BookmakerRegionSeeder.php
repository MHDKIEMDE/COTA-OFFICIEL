<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Insère / met à jour les bookmakers populaires avec leurs régions cibles.
 * Régions supportées : west_africa, central_africa, east_africa, north_africa,
 *                      europe, global
 */
class BookmakerRegionSeeder extends Seeder
{
    public function run(): void
    {
        $bookmakers = [
            // ── Afrique de l'Ouest (priorité absolue) ──────────────────────
            [
                'name'          => '1xBet',
                'slug'          => '1xbet',
                'primary_color' => '#1B5E98',
                'regions'       => ['west_africa', 'central_africa', 'north_africa', 'global'],
                'description'   => 'Inscription rapide, cotes compétitives. Bonus 100% dépôt.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 1,
            ],
            [
                'name'          => 'Betwinner',
                'slug'          => 'betwinner',
                'primary_color' => '#FF6B00',
                'regions'       => ['west_africa', 'central_africa', 'global'],
                'description'   => 'Disponible en Afrique de l\'Ouest. Bonus bienvenue généreux.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 2,
            ],
            [
                'name'          => 'Melbet',
                'slug'          => 'melbet',
                'primary_color' => '#E53935',
                'regions'       => ['west_africa', 'central_africa', 'north_africa', 'global'],
                'description'   => 'Large couverture africaine. Retrait Mobile Money.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 3,
            ],
            [
                'name'          => 'PMU Sénégal',
                'slug'          => 'pmu-senegal',
                'primary_color' => '#00875A',
                'regions'       => ['west_africa'],
                'description'   => 'Opérateur officiel au Sénégal, paiements Wave & Orange Money.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 4,
            ],
            [
                'name'          => 'Lonaci',
                'slug'          => 'lonaci',
                'primary_color' => '#F9A825',
                'regions'       => ['west_africa'],
                'description'   => 'Opérateur officiel en Côte d\'Ivoire.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 5,
            ],
            [
                'name'          => 'SportPesa',
                'slug'          => 'sportpesa',
                'primary_color' => '#004D40',
                'regions'       => ['east_africa', 'west_africa'],
                'description'   => 'Populaire en Afrique de l\'Est et de l\'Ouest.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 6,
            ],
            [
                'name'          => 'Betway Africa',
                'slug'          => 'betway-africa',
                'primary_color' => '#00A651',
                'regions'       => ['west_africa', 'east_africa', 'south_africa'],
                'description'   => 'Pari sportif fiable, app mobile disponible.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 7,
            ],
            // ── Europe ─────────────────────────────────────────────────────
            [
                'name'          => 'Bet365',
                'slug'          => 'bet365',
                'primary_color' => '#027B5B',
                'regions'       => ['europe', 'global'],
                'description'   => 'Leader mondial du pari en ligne.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 10,
            ],
            [
                'name'          => 'Betclic',
                'slug'          => 'betclic',
                'primary_color' => '#FF4F00',
                'regions'       => ['europe'],
                'description'   => 'Bookmaker européen, accessible depuis la France.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 11,
            ],
            [
                'name'          => 'Unibet',
                'slug'          => 'unibet',
                'primary_color' => '#147B45',
                'regions'       => ['europe'],
                'description'   => 'Large offre sportive en Europe.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 12,
            ],
            // ── Global / Reste du monde ────────────────────────────────────
            [
                'name'          => '22Bet',
                'slug'          => '22bet',
                'primary_color' => '#1565C0',
                'regions'       => ['west_africa', 'global'],
                'description'   => 'Disponible partout, retrait crypto et Mobile Money.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 8,
            ],
            [
                'name'          => 'Paripesa',
                'slug'          => 'paripesa',
                'primary_color' => '#6A1B9A',
                'regions'       => ['west_africa', 'global'],
                'description'   => 'Bonus compétitifs, app disponible en Afrique.',
                'affiliate_link'=> null,
                'download_link' => null,
                'sort_order'    => 9,
            ],
        ];

        foreach ($bookmakers as $data) {
            DB::table('bookmakers')->updateOrInsert(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'regions'    => json_encode($data['regions']),
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
