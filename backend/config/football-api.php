<?php

return [

    'api_key'      => env('FOOTBALL_API_KEY', ''),
    'base_url'     => env('FOOTBALL_API_BASE_URL', 'https://v3.football.api-sports.io'),
    'current_plan' => env('FOOTBALL_API_PLAN', 'free'),
    'timeout'      => 30,

    'rate_limits' => [
        'free'    => ['requests_per_day' => 100,  'requests_per_minute' => 30],
        'premium' => ['requests_per_day' => 3000, 'requests_per_minute' => 300],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => [
            'fixtures'    => 86400,
            'live_scores' => 60,
            'statistics'  => 3600,
            'standings'   => 86400,
            'teams'       => 604800,
        ],
    ],

    // Ligues de base (endpoints API)
    'leagues' => [
        39  => 'Premier League',
        140 => 'La Liga',
        135 => 'Serie A',
        78  => 'Bundesliga',
        61  => 'Ligue 1',
        2   => 'UEFA Champions League',
        3   => 'UEFA Europa League',
        848 => 'UEFA Conference League',
    ],

    // =========================================================================
    // LIGUES CIBLES — Source de verite unique, indexees par ID API-Football
    // Seuls ces championnats generent des predictions.
    // tier 1 = donnees les plus riches + cotes 1xBet garanties
    // =========================================================================
    'popular_leagues' => [
        // Tier 1 — Grandes ligues europeennes + coupes UEFA
        1   => ['name' => 'FIFA World Cup',        'country' => 'World',        'tier' => 1],
        2   => ['name' => 'UEFA Champions League', 'country' => 'Europe',       'tier' => 1],
        3   => ['name' => 'UEFA Europa League',    'country' => 'Europe',       'tier' => 1],
        39  => ['name' => 'Premier League',        'country' => 'England',      'tier' => 1],
        140 => ['name' => 'La Liga',               'country' => 'Spain',        'tier' => 1],
        135 => ['name' => 'Serie A',               'country' => 'Italy',        'tier' => 1],
        78  => ['name' => 'Bundesliga',            'country' => 'Germany',      'tier' => 1],
        61  => ['name' => 'Ligue 1',               'country' => 'France',       'tier' => 1],
        // Tier 2 — Grandes ligues secondaires
        848 => ['name' => 'UEFA Conference League','country' => 'Europe',       'tier' => 2],
        94  => ['name' => 'Primeira Liga',         'country' => 'Portugal',     'tier' => 2],
        88  => ['name' => 'Eredivisie',            'country' => 'Netherlands',  'tier' => 2],
        144 => ['name' => 'Pro League',            'country' => 'Belgium',      'tier' => 2],
        179 => ['name' => 'Scottish Premiership',  'country' => 'Scotland',     'tier' => 2],
        307 => ['name' => 'Saudi Pro League',      'country' => 'Saudi Arabia', 'tier' => 2],
        203 => ['name' => 'Supe Lig',              'country' => 'Turkey',       'tier' => 2],
        // Tier 3 — Audience Afrique + grandes ligues mondiales
        29  => ['name' => 'CAF Champions League',  'country' => 'Africa',       'tier' => 3],
        12  => ['name' => 'Africa Cup of Nations', 'country' => 'World',        'tier' => 3],
        71  => ['name' => 'Brasileirao',           'country' => 'Brazil',       'tier' => 3],
        253 => ['name' => 'MLS',                   'country' => 'USA',          'tier' => 3],
        262 => ['name' => 'Liga MX',               'country' => 'Mexico',       'tier' => 3],
    ],

    // Tier par ID — matching exact sans doublon possible
    'league_tiers_by_id' => [
        1 => 1, 2 => 1, 3 => 1, 39 => 1, 140 => 1, 135 => 1, 78 => 1, 61 => 1,
        848 => 2, 94 => 2, 88 => 2, 144 => 2, 179 => 2, 307 => 2, 203 => 2,
        29 => 3, 12 => 3, 71 => 3, 253 => 3, 262 => 3,
    ],

    // Fallback par nom (si ID non disponible dans le fixture)
    'league_tiers' => [
        'World Cup'              => 1,
        'FIFA World Cup'         => 1,
        'UEFA Champions League'  => 1,
        'Champions League'       => 1,
        'Premier League'         => 1,
        'La Liga'                => 1,
        'Serie A'                => 1,
        'Bundesliga'             => 1,
        'Ligue 1'                => 1,
        'UEFA Europa League'     => 1,
        'Europa League'          => 1,
        'UEFA Conference League' => 2,
        'Conference League'      => 2,
        'Primeira Liga'          => 2,
        'Liga Portugal'          => 2,
        'Eredivisie'             => 2,
        'Pro League'             => 2,
        'Scottish Premiership'   => 2,
        'Saudi Pro League'       => 2,
        'Super Lig'              => 2,
        'CAF Champions League'   => 3,
        'Africa Cup of Nations'  => 3,
        'AFCON'                  => 3,
        'Brasileirao'            => 3,
        'Liga MX'                => 3,
        'MLS'                    => 3,
        'Major League Soccer'    => 3,
    ],

    // Pays autorises pour les ligues ambigues (ex: "Premier League" existe partout)
    'tier1_country_whitelist' => [
        'Premier League' => 'England',
        'Serie A'        => 'Italy',
        'Bundesliga'     => 'Germany',
        'Ligue 1'        => 'France',
    ],

    'endpoints' => [
        'fixtures'         => '/fixtures',
        'fixtures_live'    => '/fixtures?live=all',
        'fixtures_date'    => '/fixtures?date={date}',
        'fixtures_team'    => '/fixtures?team={team_id}',
        'fixtures_h2h'     => '/fixtures/headtohead?h2h={team1_id}-{team2_id}',
        'teams'            => '/teams',
        'teams_statistics' => '/teams/statistics?team={team_id}&season={season}',
        'standings'        => '/standings?league={league_id}&season={season}',
        'predictions'      => '/predictions?fixture={fixture_id}',
    ],

    'timezone' => env('FOOTBALL_API_TIMEZONE', 'Africa/Ouagadougou'),

];
