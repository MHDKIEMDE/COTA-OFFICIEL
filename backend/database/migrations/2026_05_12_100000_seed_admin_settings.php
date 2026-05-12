<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppConfig;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // ── PAIEMENT ────────────────────────────────────────────────────────
            [
                'key'         => 'payment.active_provider',
                'value'       => '',
                'type'        => 'string',
                'description' => 'Slug du provider de paiement actif (ex: paydunya, cinetpay, mtn_momo…). Vide = désactivé.',
            ],
            [
                'key'         => 'payment.providers',
                'value'       => json_encode([]),
                'type'        => 'json',
                'description' => 'Liste des providers configurés. Chaque entrée : {slug, label, api_key, api_secret, env, extra}.',
            ],
            [
                'key'         => 'payment.currency',
                'value'       => 'XOF',
                'type'        => 'string',
                'description' => 'Devise utilisée pour tous les paiements (XOF, XAF, GHS…)',
            ],
            [
                'key'         => 'payment.webhook_secret',
                'value'       => '',
                'type'        => 'string',
                'description' => 'Secret partagé pour valider les webhooks entrants du provider actif.',
            ],

            // ── CLÉS API EXTERNES ────────────────────────────────────────────────
            [
                'key'         => 'api.football_api_key',
                'value'       => '',
                'type'        => 'string',
                'description' => 'Clé API-Football (api-football.com)',
            ],
            [
                'key'         => 'api.openweather_key',
                'value'       => '',
                'type'        => 'string',
                'description' => 'Clé OpenWeatherMap',
            ],
            [
                'key'         => 'api.termii_key',
                'value'       => '',
                'type'        => 'string',
                'description' => 'Clé Termii (SMS OTP)',
            ],
            [
                'key'         => 'api.termii_sender_id',
                'value'       => 'COTA',
                'type'        => 'string',
                'description' => 'Sender ID Termii affiché sur le SMS',
            ],
            [
                'key'         => 'api.facebook_app_id',
                'value'       => '',
                'type'        => 'string',
                'description' => 'App ID Facebook OAuth',
            ],
            [
                'key'         => 'api.facebook_app_secret',
                'value'       => '',
                'type'        => 'string',
                'description' => 'App Secret Facebook OAuth',
            ],

            // ── BOOKMAKERS ───────────────────────────────────────────────────────
            [
                'key'         => 'bookmakers.list',
                'value'       => json_encode([]),
                'type'        => 'json',
                'description' => 'Liste des bookmakers. Chaque entrée : {id, name, url, tracking_id, logo_emoji, color, is_active}.',
            ],

            // ── APP ──────────────────────────────────────────────────────────────
            [
                'key'         => 'app.prediction_publish_hours',
                'value'       => json_encode([8, 20]),
                'type'        => 'json',
                'description' => 'Heures de publication des pronostics (format 24h). Le mobile calcule le compte à rebours depuis ces valeurs.',
            ],
            [
                'key'         => 'app.premium_plans',
                'value'       => json_encode([
                    'weekly'    => ['label' => '7 jours',  'price' => 500,  'days' => 7],
                    'monthly'   => ['label' => '30 jours', 'price' => 1500, 'days' => 30],
                    'quarterly' => ['label' => '90 jours', 'price' => 3500, 'days' => 90],
                ]),
                'type'        => 'json',
                'description' => 'Plans premium avec prix (en XOF) et durée. Modifiable sans redéploiement.',
            ],
        ];

        foreach ($settings as $setting) {
            AppConfig::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }

    public function down(): void
    {
        $keys = [
            'payment.active_provider', 'payment.providers', 'payment.currency', 'payment.webhook_secret',
            'api.football_api_key', 'api.openweather_key', 'api.termii_key', 'api.termii_sender_id',
            'api.facebook_app_id', 'api.facebook_app_secret',
            'bookmakers.list',
            'app.prediction_publish_hours', 'app.premium_plans',
        ];

        AppConfig::whereIn('key', $keys)->delete();
    }
};
