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

    // Ligues populaires avec niveau de priorité (tier 1 = plus populaire)
    'popular_leagues' => [
        // Tier 1 — Top 5 européennes
        2   => ['name' => 'Champions League',      'country' => 'Europe',       'tier' => 1],
        39  => ['name' => 'Premier League',         'country' => 'England',      'tier' => 1],
        140 => ['name' => 'La Liga',                'country' => 'Spain',        'tier' => 1],
        135 => ['name' => 'Serie A',                'country' => 'Italy',        'tier' => 1],
        78  => ['name' => 'Bundesliga',             'country' => 'Germany',      'tier' => 1],
        61  => ['name' => 'Ligue 1',                'country' => 'France',       'tier' => 1],
        // Tier 2 — Grandes compétitions européennes & ligues secondaires
        3   => ['name' => 'Europa League',          'country' => 'Europe',       'tier' => 2],
        848 => ['name' => 'Conference League',      'country' => 'Europe',       'tier' => 2],
        94  => ['name' => 'Liga Portugal',          'country' => 'Portugal',     'tier' => 2],
        88  => ['name' => 'Eredivisie',             'country' => 'Netherlands',  'tier' => 2],
        144 => ['name' => 'Pro League',             'country' => 'Belgium',      'tier' => 2],
        179 => ['name' => 'Scottish Premiership',   'country' => 'Scotland',     'tier' => 2],
        307 => ['name' => 'Saudi Pro League',       'country' => 'Saudi Arabia', 'tier' => 2],
        // Tier 3 — Ligues majeures hors Europe
        253 => ['name' => 'MLS',                   'country' => 'USA',          'tier' => 3],
        71  => ['name' => 'Brasileirao',            'country' => 'Brazil',       'tier' => 3],
        262 => ['name' => 'Liga MX',               'country' => 'Mexico',       'tier' => 3],
        203 => ['name' => 'Süper Lig',             'country' => 'Turkey',       'tier' => 3],
        // Tier 4 — Afrique & reste du monde
        12  => ['name' => 'AFCON',                 'country' => 'Africa',       'tier' => 4],
        17  => ['name' => 'AFCON Qualification',   'country' => 'Africa',       'tier' => 4],
        29  => ['name' => 'CAF Champions League',  'country' => 'Africa',       'tier' => 4],
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
