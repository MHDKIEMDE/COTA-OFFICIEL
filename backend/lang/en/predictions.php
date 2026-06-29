<?php

declare(strict_types=1);

/**
 * Localized bet labels (EN).
 * Used by App\Services\BetLabelTranslator to translate outcomes produced
 * by PredictionAlgorithmService at display time (API).
 *
 * Placeholders:
 *   :team → team name
 *   :line → numeric line (e.g. 2.5)
 */
return [
    // ── Bet types ────────────────────────────────────────────────────
    'types' => [
        '1X2'           => 'Match result',
        'Double Chance' => 'Double chance',
        'BTTS'          => 'Both teams to score',
        'Over/Under'    => 'Over / Under goals',
        'Team Goals'    => 'Team goals',
        'Corners'       => 'Corners',
        'Cards'         => 'Cards',
        'Shots'         => 'Shots',
        'Handicap'      => 'Handicap',
    ],

    // ── 1X2 ──────────────────────────────────────────────────────────
    '1'  => 'Home win',
    'X'  => 'Draw',
    '2'  => 'Away win',

    // ── Double chance ────────────────────────────────────────────────
    '1X' => 'Home or draw',
    'X2' => 'Draw or away',
    '12' => 'Home or away',

    // ── BTTS ─────────────────────────────────────────────────────────
    'btts_yes' => 'Yes',
    'btts_no'  => 'No',

    // ── Over / Under goals ───────────────────────────────────────────
    'over'  => 'Over :line goals',
    'under' => 'Under :line goals',

    // ── Corners ──────────────────────────────────────────────────────
    'corners_over'  => 'Over :line corners',
    'corners_under' => 'Under :line corners',

    // ── Cards ────────────────────────────────────────────────────────
    'cards_over'  => 'Over :line cards',
    'cards_under' => 'Under :line cards',

    // ── Shots ────────────────────────────────────────────────────────
    'shots_total_under' => 'Under :line total shots',
    'team_shots_over'   => ':team — over :line shots',

    // ── Team goals ───────────────────────────────────────────────────
    'team_over'  => ':team — over :line goal(s)',
    'team_under' => ':team — under :line goal(s)',
];
