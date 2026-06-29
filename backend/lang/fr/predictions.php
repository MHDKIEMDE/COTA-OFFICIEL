<?php

declare(strict_types=1);

/**
 * Libellés de paris localisés (FR).
 * Utilisés par App\Services\BetLabelTranslator pour traduire les outcomes
 * produits par PredictionAlgorithmService à l'affichage (API).
 *
 * Les placeholders :
 *   :team → nom de l'équipe concernée
 *   :line → ligne numérique (ex. 2.5)
 */
return [
    // ── Types de paris ───────────────────────────────────────────────
    'types' => [
        '1X2'           => 'Résultat du match',
        'Double Chance' => 'Double chance',
        'BTTS'          => 'Les deux marquent',
        'Over/Under'    => 'Plus / Moins de buts',
        'Team Goals'    => 'Buts par équipe',
        'Corners'       => 'Corners',
        'Cards'         => 'Cartons',
        'Shots'         => 'Tirs',
        'Handicap'      => 'Handicap',
    ],

    // ── 1X2 ──────────────────────────────────────────────────────────
    '1'  => 'Victoire domicile',
    'X'  => 'Match nul',
    '2'  => 'Victoire extérieur',

    // ── Double chance ────────────────────────────────────────────────
    '1X' => 'Domicile ou nul',
    'X2' => 'Nul ou extérieur',
    '12' => 'Domicile ou extérieur',

    // ── BTTS ─────────────────────────────────────────────────────────
    'btts_yes' => 'Oui',
    'btts_no'  => 'Non',

    // ── Over / Under buts ────────────────────────────────────────────
    'over'  => 'Plus de :line buts',
    'under' => 'Moins de :line buts',

    // ── Corners ──────────────────────────────────────────────────────
    'corners_over'  => 'Plus de :line corners',
    'corners_under' => 'Moins de :line corners',

    // ── Cartons ──────────────────────────────────────────────────────
    'cards_over'  => 'Plus de :line cartons',
    'cards_under' => 'Moins de :line cartons',

    // ── Tirs ─────────────────────────────────────────────────────────
    'shots_total_under' => 'Moins de :line tirs au total',
    'team_shots_over'   => ':team — plus de :line tirs',

    // ── Buts par équipe ──────────────────────────────────────────────
    'team_over'  => ':team — plus de :line but(s)',
    'team_under' => ':team — moins de :line but(s)',
];
