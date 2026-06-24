<?php

/**
 * Configuration COTA — quotas et seuils de publication
 * Tous les paramètres ici sont modifiables sans toucher au code.
 * En production, surcharger via les variables d'environnement ou AppConfig DB.
 */
return [

    // ── Désactivation globale des verrous auth/premium (dev/local uniquement) ─
    // true → tout utilisateur (même invité) est traité comme premium :
    // coupon complet, combiné du jour, aucune variante verrouillée.
    'disable_locks' => (bool) env('COTA_DISABLE_LOCKS', false),

    // ── Seuils de score_tier (A2 CDC v3.1) ──────────────────────────────────
    'tiers' => [
        'gold'     => (float) env('COTA_TIER_GOLD',     65),
        'standard' => (float) env('COTA_TIER_STANDARD', 50),
        'bronze'   => (float) env('COTA_TIER_BRONZE',   35),
    ],

    // ── Quotas de publication par pool (A3 CDC v3.1) ────────────────────────
    'pools' => [
        'free' => [
            'min'        => (int) env('COTA_FREE_MIN', 4),
            'max'        => (int) env('COTA_FREE_MAX', 6),
            // Niveaux acceptés : gold + standard uniquement
            'min_score'  => (float) env('COTA_FREE_MIN_SCORE', 50),
        ],
        'premium' => [
            'min'        => (int) env('COTA_PREMIUM_MIN', 12),
            'max'        => (int) env('COTA_PREMIUM_MAX', 18),
            // Niveaux acceptés : gold + standard + bronze
            'min_score'  => (float) env('COTA_PREMIUM_MIN_SCORE', 35),
        ],
    ],

    // ── Plancher de sécurité (A3 CDC v3.1) ──────────────────────────────────
    // Si moins de X prédictions ≥ min_score_free ce jour, réduire le coupon free
    'safety_floor' => [
        'min_qualified'    => (int) env('COTA_FLOOR_MIN_QUALIFIED', 3),
        'reduced_free_max' => (int) env('COTA_FLOOR_REDUCED_MAX',   4),
    ],

    // ── Règles de diversité (A3 CDC v3.1) ───────────────────────────────────
    'diversity' => [
        // Max X prédictions du même type de marché dans un coupon
        'max_same_market'      => (int) env('COTA_DIV_MAX_SAME_MARKET', 2),
        // Max X prédictions de la même compétition dans un coupon
        'max_same_competition' => (int) env('COTA_DIV_MAX_SAME_COMP',   2),
        // Coupon free : nombre minimum de familles de marchés différentes
        'min_market_families'  => (int) env('COTA_DIV_MIN_FAMILIES',    3),
    ],

    // ── Mode largeur/profondeur (A3 CDC v3.1) ───────────────────────────────
    // Si le nb de matchs qualifiés est ≤ ce seuil → mode profondeur
    'depth_mode_threshold' => (int) env('COTA_DEPTH_THRESHOLD', 2),

    // ── Coupon Sûr / Audacieux (T4 CDC v3.1) ────────────────────────────────
    'coupon' => [
        'safe' => [
            'picks'      => (int) env('COTA_SAFE_PICKS', 5),
            'odds_min'   => (float) env('COTA_SAFE_ODDS_MIN', 1.20),
            'odds_max'   => (float) env('COTA_SAFE_ODDS_MAX', 1.80),
            'target_total_min' => (float) env('COTA_SAFE_TOTAL_MIN', 8.0),
            'target_total_max' => (float) env('COTA_SAFE_TOTAL_MAX', 10.0),
        ],
        'bold' => [
            'picks'      => (int) env('COTA_BOLD_PICKS', 5),
            'odds_min'   => (float) env('COTA_BOLD_ODDS_MIN', 2.50),
        ],
        // Coupon Kamikaze (Premium) : combiné très haut risque / très haute cote.
        // Beaucoup de picks, cotes élevées → gain potentiel énorme, proba faible.
        'kamikaze' => [
            // Volume : on empile beaucoup de picks (même cote unitaire modeste)
            // pour viser une grosse cote TOTALE. Le caractère "kamikaze" vient du
            // nombre de matchs combinés, pas de la cote unitaire.
            'picks'      => (int) env('COTA_KAMIKAZE_PICKS', 10),
            'odds_min'   => (float) env('COTA_KAMIKAZE_ODDS_MIN', 1.10),
            'total_min'  => (float) env('COTA_KAMIKAZE_TOTAL_MIN', 12.0),
        ],
        'free' => [
            'picks'      => (int) env('COTA_FREE_PICKS', 5),
            'total_min'  => (float) env('COTA_FREE_TOTAL_MIN', 6.0),
            'total_max'  => (float) env('COTA_FREE_TOTAL_MAX', 12.0),
        ],
    ],
];
