<?php

namespace Database\Seeders;

use App\Models\CouponTab;
use Illuminate\Database\Seeder;

class CouponTabSeeder extends Seeder
{
    public function run(): void
    {
        $tabs = [
            ['key' => 'prudent',   'label' => 'Prudent',    'subtitle' => '3–6x',    'sort_order' => 1],
            ['key' => 'equilibre', 'label' => 'Équilibré',  'subtitle' => '8–15x',   'sort_order' => 2],
            ['key' => 'kamikaze',  'label' => 'Kamikaze',   'subtitle' => '15x+',    'sort_order' => 3],
            // Onglet vedette : son libellé sert de repli quand aucune compétition
            // n'est en cours ; sinon le mobile affiche le nom réel de la compétition.
            ['key' => 'featured',  'label' => '🏆 Vedette', 'subtitle' => 'spécial', 'sort_order' => 4],
        ];

        foreach ($tabs as $tab) {
            CouponTab::updateOrCreate(['key' => $tab['key']], $tab);
        }
    }
}
