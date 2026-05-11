<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Compétitions Prioritaires / Tendance
    |--------------------------------------------------------------------------
    |
    | Liste des compétitions à mettre en avant sur le site.
    | IDs Sportradar réels utilisés par l'API.
    |
    */

    'priority_competitions' => [
        
        // =============================================
        // 🏆 CAN 2025 - COUPE D'AFRIQUE DES NATIONS
        // =============================================
        
        'sr:competition:270' => [
            'name' => 'CAN 2025',
            'full_name' => 'Coupe d\'Afrique des Nations',
            'priority' => 1,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Africa',
            'trending' => true,
        ],
        
        // =============================================
        // 🏴󠁧󠁢󠁥󠁮󠁧󠁿 ANGLETERRE - PREMIER LEAGUE
        // =============================================
        
        'sr:competition:17' => [
            'name' => 'Premier League',
            'full_name' => 'English Premier League',
            'priority' => 2,
            'active' => true,
            'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'country' => 'England',
            'trending' => true,
        ],
        
        'sr:competition:327' => [
            'name' => 'EFL Cup',
            'full_name' => 'EFL Cup (Carabao Cup)',
            'priority' => 5,
            'active' => true,
            'icon' => '🏆',
            'country' => 'England',
        ],
        
        // =============================================
        // 🇮🇹 ITALIE - SERIE A
        // =============================================
        
        'sr:competition:23' => [
            'name' => 'Serie A',
            'full_name' => 'Serie A Italiana',
            'priority' => 2,
            'active' => true,
            'icon' => '🇮🇹',
            'country' => 'Italy',
            'trending' => true,
        ],
        
        'sr:competition:329' => [
            'name' => 'Coppa Italia',
            'full_name' => 'Coppa Italia',
            'priority' => 5,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Italy',
        ],
        
        // =============================================
        // 🇫🇷 FRANCE - LIGUE 1
        // =============================================
        
        'sr:competition:1226' => [
            'name' => 'Ligue 1',
            'full_name' => 'Ligue 1 Française',
            'priority' => 2,
            'active' => true,
            'icon' => '🇫🇷',
            'country' => 'France',
            'trending' => true,
        ],
        
        // =============================================
        // 🇪🇸 ESPAGNE - LA LIGA (à vérifier l'ID)
        // =============================================
        
        'sr:competition:8' => [
            'name' => 'La Liga',
            'full_name' => 'La Liga Española',
            'priority' => 2,
            'active' => true,
            'icon' => '🇪🇸',
            'country' => 'Spain',
            'trending' => true,
        ],
        
        'sr:competition:301' => [
            'name' => 'Copa del Rey',
            'full_name' => 'Copa del Rey',
            'priority' => 5,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Spain',
        ],
        
        // =============================================
        // 🇩🇪 ALLEMAGNE - BUNDESLIGA (à vérifier l'ID)
        // =============================================
        
        'sr:competition:35' => [
            'name' => 'Bundesliga',
            'full_name' => 'Bundesliga Allemande',
            'priority' => 2,
            'active' => true,
            'icon' => '🇩🇪',
            'country' => 'Germany',
            'trending' => true,
        ],
        
        // =============================================
        // 🏆 COMPÉTITIONS EUROPÉENNES
        // =============================================
        
        'sr:competition:7' => [
            'name' => 'Champions League',
            'full_name' => 'UEFA Champions League',
            'priority' => 1,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Europe',
            'trending' => true,
        ],
        
        'sr:competition:679' => [
            'name' => 'Europa League',
            'full_name' => 'UEFA Europa League',
            'priority' => 2,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Europe',
        ],
        
        'sr:competition:1371' => [
            'name' => 'Conference League',
            'full_name' => 'UEFA Conference League',
            'priority' => 3,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Europe',
        ],
        
        // =============================================
        // 🇵🇹 PORTUGAL
        // =============================================
        
        'sr:competition:238' => [
            'name' => 'Primeira Liga',
            'full_name' => 'Liga Portugal',
            'priority' => 4,
            'active' => true,
            'icon' => '🇵🇹',
            'country' => 'Portugal',
        ],
        
        // =============================================
        // 🇳🇱 PAYS-BAS
        // =============================================
        
        'sr:competition:37' => [
            'name' => 'Eredivisie',
            'full_name' => 'Eredivisie',
            'priority' => 4,
            'active' => true,
            'icon' => '🇳🇱',
            'country' => 'Netherlands',
        ],
        
        // =============================================
        // 🌍 AFRIQUE
        // =============================================
        
        'sr:competition:1093' => [
            'name' => 'CAF Champions League',
            'full_name' => 'CAF Champions League',
            'priority' => 3,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Africa',
        ],
        
        // =============================================
        // 🌎 AMÉRIQUE DU SUD
        // =============================================
        
        'sr:competition:384' => [
            'name' => 'Copa Libertadores',
            'full_name' => 'Copa Libertadores',
            'priority' => 4,
            'active' => true,
            'icon' => '🏆',
            'country' => 'South America',
        ],
        
        'sr:competition:325' => [
            'name' => 'Brasileirão',
            'full_name' => 'Campeonato Brasileiro Série A',
            'priority' => 5,
            'active' => true,
            'icon' => '🇧🇷',
            'country' => 'Brazil',
        ],
        
        // =============================================
        // 🌏 ASIE
        // =============================================
        
        'sr:competition:2430' => [
            'name' => 'AFC U23 Asian Cup',
            'full_name' => 'AFC U23 Asian Cup',
            'priority' => 6,
            'active' => true,
            'icon' => '🏆',
            'country' => 'Asia',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Compétitions Tendance (à mettre en avant en ce moment)
    |--------------------------------------------------------------------------
    */
    'trending' => [
        'sr:competition:270',   // CAN 2025 🔥
        'sr:competition:17',    // Premier League
        'sr:competition:23',    // Serie A
        'sr:competition:1226',  // Ligue 1
        'sr:competition:8',     // La Liga
        'sr:competition:35',    // Bundesliga
        'sr:competition:7',     // Champions League
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtrer uniquement les compétitions prioritaires
    |--------------------------------------------------------------------------
    */
    'filter_only_priority' => false,

    /*
    |--------------------------------------------------------------------------
    | Nombre maximum de matchs par compétition
    |--------------------------------------------------------------------------
    */
    'max_matches_per_competition' => 20,

    /*
    |--------------------------------------------------------------------------
    | Ordre d'affichage sur le dashboard
    |--------------------------------------------------------------------------
    */
    'dashboard_order' => 'priority',
];
