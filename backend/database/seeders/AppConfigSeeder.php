<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use Illuminate\Database\Seeder;

class AppConfigSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // --- Paiement ---
            'payment.active_provider' => 'paydunya',

            'payment.providers' => json_encode([
                'paydunya' => [
                    'master_key'   => '',
                    'private_key'  => '',
                    'token'        => '',
                    'mode'         => 'test',
                ],
                'cinetpay' => [
                    'api_key'    => '',
                    'site_id'    => '',
                    'secret_key' => '',
                    'mode'       => 'test',
                ],
            ]),

            // --- Plans premium ---
            'app.premium_plans' => json_encode([
                'weekly' => [
                    'id'       => 'weekly',
                    'name'     => 'Hebdomadaire',
                    'label'    => 'Abonnement 7 jours',
                    'duration' => '7 jours',
                    'price'    => 2500,
                    'currency' => 'FCFA',
                ],
                'monthly' => [
                    'id'          => 'monthly',
                    'name'        => 'Mensuel',
                    'label'       => 'Abonnement 30 jours',
                    'duration'    => '30 jours',
                    'price'       => 8000,
                    'currency'    => 'FCFA',
                    'savings'     => '20%',
                    'recommended' => true,
                ],
                'quarterly' => [
                    'id'       => 'quarterly',
                    'name'     => 'Trimestriel',
                    'label'    => 'Abonnement 90 jours',
                    'duration' => '90 jours',
                    'price'    => 20000,
                    'currency' => 'FCFA',
                    'savings'  => '33%',
                ],
            ]),

            // --- Application ---
            'app.name'        => 'COTA',
            'app.currency'    => 'FCFA',
            'app.maintenance' => '0',
        ];

        foreach ($defaults as $key => $value) {
            AppConfig::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
