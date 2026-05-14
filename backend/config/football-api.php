<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API-Football Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration avec API-Football (api-football.com)
    | Fournit des données en temps réel sur les matchs de football
    |
    */

    // Clé API (à obtenir sur https://www.api-football.com/)
    'api_key' => env('FOOTBALL_API_KEY', ''),

    // URL de base de l'API
    'base_url' => env('FOOTBALL_API_BASE_URL', 'https://v3.football.api-sports.io'),

    // Configuration des limites de l'API
    'rate_limits' => [
        // Plan gratuit: 100 requêtes/jour
        'free' => [
            'requests_per_day' => 100,
            'requests_per_minute' => 30,
        ],
        // Plan payant peut être configuré ici
        'premium' => [
            'requests_per_day' => 3000,
            'requests_per_minute' => 300,
        ],
    ],

    // Plan actuel
    'current_plan' => env('FOOTBALL_API_PLAN', 'free'),

    // Timeout des requêtes (en secondes)
    'timeout' => 30,

    // Cache configuration
    'cache' => [
        'enabled' => true,
        'ttl' => [
            'fixtures' => 300, // 5 minutes
            'live_scores' => 60, // 1 minute
            'statistics' => 3600, // 1 heure
            'standings' => 86400, // 24 heures
            'teams' => 604800, // 7 jours
        ],
    ],

    // Leagues/Compétitions à suivre
    'leagues' => [
        39 => 'Premier League', // Angleterre
        140 => 'La Liga', // Espagne
        135 => 'Serie A', // Italie
        78 => 'Bundesliga', // Allemagne
        61 => 'Ligue 1', // France
        2 => 'UEFA Champions League',
        3 => 'UEFA Europa League',
        848 => 'UEFA Conference League',
    ],

    // Priorité des ligues par nom exact retourné par l'API (tier 1 = plus populaire, 99 = inconnu)
    // Clé = nom exact tel que retourné par API-Football
    'league_tiers' => [
        // Tier 1 — Priorité absolue
        'UEFA Champions League'  => 1,
        'Champions League'       => 1,
        'Premier League'         => 1,  // England uniquement — filtré par country dans le job
        'La Liga'                => 1,
        'Serie A'                => 1,  // Italy uniquement
        'Bundesliga'             => 1,  // Germany uniquement
        'Ligue 1'                => 1,  // France uniquement
        // Tier 2 — Grandes compétitions
        'UEFA Europa League'     => 2,
        'Europa League'          => 2,
        'UEFA Conference League' => 2,
        'Conference League'      => 2,
        'Liga Portugal'          => 2,
        'Eredivisie'             => 2,
        'Pro League'             => 2,
        'Scottish Premiership'   => 2,
        'Saudi Pro League'       => 2,
        'Primeira Liga'          => 2,
        // Tier 3 — Ligues majeures hors Europe
        'Major League Soccer'    => 3,
        'MLS'                    => 3,
        'Brasileirao'            => 3,
        'Série A'                => 3,
        'Liga MX'                => 3,
        'Süper Lig'              => 3,
        'Super Lig'              => 3,
        // Tier 4 — Afrique & reste
        'Africa Cup of Nations'  => 4,
        'AFCON'                  => 4,
        'CAF Champions League'   => 4,
        'CAF Cup of Nations'     => 4,
    ],

    // Pays autorisés pour les ligues ambiguës (ex: "Premier League" existe partout)
    'tier1_country_whitelist' => [
        'Premier League' => 'England',
        'Serie A'        => 'Italy',
        'Bundesliga'     => 'Germany',
        'Ligue 1'        => 'France',
        'Première Division' => 'France',
    ],

    // Endpoints disponibles
    'endpoints' => [
        'fixtures' => '/fixtures',
        'fixtures_live' => '/fixtures?live=all',
        'fixtures_date' => '/fixtures?date={date}',
        'fixtures_team' => '/fixtures?team={team_id}',
        'fixtures_h2h' => '/fixtures/headtohead?h2h={team1_id}-{team2_id}',
        'teams' => '/teams',
        'teams_statistics' => '/teams/statistics?team={team_id}&season={season}',
        'standings' => '/standings?league={league_id}&season={season}',
        'predictions' => '/predictions?fixture={fixture_id}',
    ],

    // Timezone pour les dates
    'timezone' => env('FOOTBALL_API_TIMEZONE', 'Africa/Ouagadougou'),

];
