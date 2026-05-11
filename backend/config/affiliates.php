<?php

/**
 * Configuration des affiliations bookmakers
 * 
 * Pour chaque bookmaker, configurez:
 * - tracking_url: URL de base du tracking AffiliateControl
 * - bonus_days: Jours premium offerts à l'utilisateur
 * 
 * Les URLs de tracking doivent être obtenues depuis votre dashboard AffiliateControl
 * dans la section "Traffic Sources" > "Tracking Links"
 */

return [

    /*
    |--------------------------------------------------------------------------
    | BetWinner
    |--------------------------------------------------------------------------
    */
    'betwinner' => [
        'name' => 'BetWinner',
        'tracking_url' => env('AFFILIATE_BETWINNER_URL', 'https://bwredir.com/1ABC'),
        'bonus_days' => env('AFFILIATE_BETWINNER_BONUS_DAYS', 7),
        'enabled' => env('AFFILIATE_BETWINNER_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | 1xBet
    |--------------------------------------------------------------------------
    */
    '1xbet' => [
        'name' => '1xBet',
        'tracking_url' => env('AFFILIATE_1XBET_URL', 'https://refpa.top/1XYZ'),
        'bonus_days' => env('AFFILIATE_1XBET_BONUS_DAYS', 7),
        'enabled' => env('AFFILIATE_1XBET_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Melbet
    |--------------------------------------------------------------------------
    */
    'melbet' => [
        'name' => 'Melbet',
        'tracking_url' => env('AFFILIATE_MELBET_URL', 'https://melredir.com/1DEF'),
        'bonus_days' => env('AFFILIATE_MELBET_BONUS_DAYS', 7),
        'enabled' => env('AFFILIATE_MELBET_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration générale
    |--------------------------------------------------------------------------
    */
    'default_bonus_days' => env('AFFILIATE_DEFAULT_BONUS_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | API AffiliateControl
    |--------------------------------------------------------------------------
    |
    | Credentials pour accéder à l'API AffiliateControl
    | Permet de vérifier si un ID joueur est dans nos conversions
    | 
    | Obtenir les clés: Dashboard AffiliateControl > API Keys > Create
    */
    'api' => [
        'customer_id' => env('AFFILIATECONTROL_CUSTOMER_ID'),
        'access_key' => env('AFFILIATECONTROL_ACCESS_KEY'),
        'secret_key' => env('AFFILIATECONTROL_SECRET_KEY'),
        'base_url' => env('AFFILIATECONTROL_BASE_URL', 'https://affiliatecontrol-api.com/affiliates'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité Webhook
    |--------------------------------------------------------------------------
    | 
    | IPs autorisées pour les webhooks AffiliateControl (optionnel)
    | Si vide, tous les IPs sont acceptés (vérification désactivée)
    */
    'webhook_allowed_ips' => array_filter(explode(',', env('AFFILIATE_WEBHOOK_IPS', ''))),

    /*
    |--------------------------------------------------------------------------
    | URL de callback pour les postbacks
    |--------------------------------------------------------------------------
    |
    | Cette URL doit être configurée dans AffiliateControl:
    | Traffic Sources > Postbacks > Add Postback
    |
    | Exemple d'URL à configurer:
    | https://api.votredomaine.com/api/webhooks/affiliate?extid={extid}&eventType={eventType}&playerId={playerId}&revenue={revenue}&requestId={requestId}&subid1={subid1}&subid2={subid2}
    */
    'postback_url' => env('APP_URL') . '/api/webhooks/affiliate',

];

