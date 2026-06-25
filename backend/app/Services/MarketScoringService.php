<?php

namespace App\Services;

/**
 * MarketScoringService — T2 CDC v3.1
 *
 * Responsabilités :
 * - Convertit un candidat marché (issu des moteurs de PredictionAlgorithmService)
 *   en score normalisé 0–100
 * - Calcule le score_tier (gold ≥65 / standard 50–64 / bronze 35–49)
 * - Calcule active_side selon le mapping market_selection → équipe UI
 * - Applique la repondération mode tournoi international
 */
class MarketScoringService
{
    // Seuils tier A2 CDC v3.1
    const TIER_GOLD = 65.0;

    const TIER_STANDARD = 50.0;

    const TIER_BRONZE = 35.0;

    // Mapping bet_type → famille de marché (pour le switch UI mobile)
    const CATEGORY_MAP = [
        '1X2' => 'resultat',
        'Double Chance' => 'resultat',
        'Handicap' => 'resultat',
        'Over/Under' => 'buts',
        'BTTS' => 'buts',
        'Team Goals' => 'equipe',
        'Score Exact' => 'score',
        'Corners' => 'corners',
        'Cards' => 'cartons',
        'Shots' => 'tirs',
    ];

    // Mapping market_selection → active_side
    const ACTIVE_SIDE_MAP = [
        '1' => 'home',
        '2' => 'away',
        'X' => 'none',
        '1X' => 'home',
        'X2' => 'away',
        '12' => 'both',
        // Marchés buts / stats → pas d'équipe illuminée
        'Oui' => 'both',  // BTTS oui
        'Non' => 'none',  // BTTS non
    ];

    /**
     * Calcule le market_score (0–100) depuis la confidence brute d'un candidat.
     *
     * La confidence dans makeCandidate() est déjà sur ~0–100 mais non bornée —
     * on la normalise proprement ici.
     */
    public function normalizeScore(float $rawConfidence): float
    {
        return round(min(max($rawConfidence, 0.0), 100.0), 2);
    }

    /**
     * Détermine le score_tier à partir d'un market_score normalisé.
     */
    public function scoreTier(float $marketScore): ?string
    {
        if ($marketScore >= self::TIER_GOLD) {
            return 'gold';
        }
        if ($marketScore >= self::TIER_STANDARD) {
            return 'standard';
        }
        if ($marketScore >= self::TIER_BRONZE) {
            return 'bronze';
        }

        return null; // jeté — ne pas publier
    }

    /**
     * Détermine active_side à partir de la sélection exacte du marché.
     *
     * Règle :
     * - 1X2, Double Chance → mapping direct via ACTIVE_SIDE_MAP
     * - Over/Under buts, Corners, Cards, Shots, Score Exact → none
     * - BTTS Oui → both, BTTS Non → none
     * - Team Goals "{Équipe} Over X" → home si home_name dans outcome, sinon away
     */
    public function activeSide(string $betType, string $outcome, string $homeName = '', string $awayName = ''): string
    {
        // Marchés purement statistiques — aucune équipe illuminée
        $statMarkets = ['Over/Under', 'Corners', 'Cards', 'Shots', 'Score Exact', 'Handicap'];
        if (in_array($betType, $statMarkets)) {
            return 'none';
        }

        // BTTS
        if ($betType === 'BTTS') {
            return $outcome === 'Oui' ? 'both' : 'none';
        }

        // Team Goals — dériver de l'outcome
        if ($betType === 'Team Goals') {
            if ($homeName !== '' && str_starts_with($outcome, $homeName)) {
                return 'home';
            }
            if ($awayName !== '' && str_starts_with($outcome, $awayName)) {
                return 'away';
            }

            return 'none';
        }

        // 1X2 / Double Chance — mapping direct
        return self::ACTIVE_SIDE_MAP[$outcome] ?? 'none';
    }

    /**
     * Sélectionne le meilleur candidat depuis la liste, en appliquant éventuellement
     * le mode tournoi, et retourne les champs A1/A2 enrichis.
     *
     * @param  array  $candidates  Liste de candidats issus des moteurs (format makeCandidate)
     * @param  float  $totalScore  Score total de l'algorithme 9 critères (0–100)
     * @param  bool  $isTournament  true si competition.type == "international"
     * @param  float  $fifaGap  Différentiel de ranking FIFA (home - away, normalisé 0–1)
     */
    public function bestMarketFor(
        array $candidates,
        float $totalScore,
        bool $isTournament = false,
        float $fifaGap = 0.0,
        string $homeName = '',
        string $awayName = ''
    ): array {
        if (empty($candidates)) {
            return $this->fallback($totalScore, $homeName);
        }

        // Mode tournoi : booster les candidats Force (1X2/DC) si FIFA gap fort,
        // et pénaliser les marchés dom/ext qui n'ont pas de sens sur terrain neutre
        if ($isTournament) {
            $candidates = $this->applyTournamentWeighting($candidates, $fifaGap);
        }

        // Choisir le candidat au market_value le plus élevé (déjà calculé),
        // mais jamais un pari haute-variance (Score Exact) comme marché principal.
        usort($candidates, fn ($a, $b) => $b['market_value'] <=> $a['market_value']);
        $safe = array_values(array_filter(
            $candidates,
            fn ($c) => ($c['engine'] ?? '') !== 'high_variance' && ! ($c['is_risky'] ?? false)
        ));
        $best = ! empty($safe) ? reset($safe) : reset($candidates);

        $marketScore = $this->normalizeScore($best['market_value'] * 100);

        // En tournoi on peut booster légèrement le score si FIFA gap confirme
        if ($isTournament && abs($fifaGap) >= 0.3) {
            $marketScore = min($marketScore * 1.10, 100.0);
        }

        $tier = $this->scoreTier($marketScore);
        $activeSide = $this->activeSide($best['type'], $best['outcome'], $homeName, $awayName);

        return [
            'type' => $best['type'],
            'outcome' => $best['outcome'],
            'odds' => $best['odds'],
            'engine' => $best['engine'],
            'market_value' => $best['market_value'],
            // Champs A1
            'market_score' => round($marketScore, 2),
            'score_tier' => $tier,
            'active_side' => $activeSide,
            // market_selection = outcome normalisé en code universel
            'market_selection' => $this->toUniversalCode($best['type'], $best['outcome']),
        ];
    }

    /**
     * Score TOUS les candidats et retourne la liste complète des marchés à persister.
     *
     * Contrairement à bestMarketFor() qui ne garde que le meilleur, ici on conserve
     * chaque candidat publiable (tier non null) pour alimenter le switch multi-marchés.
     *
     * - Déduplique par (type + outcome) en gardant le meilleur score
     * - Marque is_primary sur le candidat au market_value le plus élevé
     * - Reporte is_risky depuis le moteur underdog
     * - Catégorise via CATEGORY_MAP pour le filtre UI
     *
     * @param  array  $candidates  Candidats issus de buildAllCandidates()
     * @return array<int,array> Liste prête à insérer dans prediction_markets
     */
    public function allMarketsFor(
        array $candidates,
        bool $isTournament = false,
        float $fifaGap = 0.0,
        string $homeName = '',
        string $awayName = ''
    ): array {
        if (empty($candidates)) {
            return [];
        }

        if ($isTournament) {
            $candidates = $this->applyTournamentWeighting($candidates, $fifaGap);
        }

        // Déduplication : garder le meilleur score par (type|outcome)
        $unique = [];
        foreach ($candidates as $c) {
            $key = $c['type'].'|'.$c['outcome'];
            if (! isset($unique[$key]) || ($c['confidence'] ?? 0) > ($unique[$key]['confidence'] ?? 0)) {
                $unique[$key] = $c;
            }
        }
        $candidates = array_values($unique);

        // Construire les marchés publiables (tier non null)
        $markets = [];
        foreach ($candidates as $c) {
            $marketScore = $this->normalizeScore(($c['confidence'] ?? ($c['market_value'] * 100)));
            $tier = $this->scoreTier($marketScore);

            if ($tier === null) {
                continue; // non publiable — on ne stocke pas le bruit
            }

            $markets[] = [
                'category' => self::CATEGORY_MAP[$c['type']] ?? 'autre',
                'bet_type' => $c['type'],
                'outcome' => $c['outcome'],
                'market_selection' => $this->toUniversalCode($c['type'], $c['outcome']),
                'odds' => $c['odds'],
                'market_score' => $marketScore,
                'score_tier' => $tier,
                'active_side' => $this->activeSide($c['type'], $c['outcome'], $homeName, $awayName),
                'engine' => $c['engine'],
                'is_primary' => false,
                'is_risky' => $c['is_risky'] ?? false,
            ];
        }

        // Marché principal = le plus fiable, hors paris risqués / haute-variance.
        // Tri d'affichage : score décroissant.
        usort($markets, fn ($a, $b) => $b['market_score'] <=> $a['market_score']);

        foreach ($markets as $i => $m) {
            if (! $m['is_risky'] && $m['engine'] !== 'high_variance') {
                $markets[$i]['is_primary'] = true;
                break;
            }
        }

        return $markets;
    }

    /**
     * Repondération mode tournoi international (A1 CDC v3.1).
     *
     * - Pénalise les marchés dom/ext (Team Goals, Home/Away) car terrain neutre
     * - Booste les candidats force (1X2, DC) si FIFA gap > 0.3
     * - Ne touche pas aux marchés buts (Over/Under, BTTS) car fiables en tournoi
     */
    private function applyTournamentWeighting(array $candidates, float $fifaGap): array
    {
        return array_map(function (array $c) use ($fifaGap): array {
            $mv = $c['market_value'];

            // Pénaliser les marchés qui dépendent du contexte dom/ext
            if ($c['engine'] === 'team_goals') {
                $mv *= 0.70;
            }

            // Booster force si le différentiel FIFA confirme une équipe dominante
            if ($c['engine'] === 'force' && abs($fifaGap) >= 0.30) {
                $mv *= 1.15;
            }

            // Les marchés buts restent neutres
            $c['market_value'] = round($mv, 4);

            return $c;
        }, $candidates);
    }

    /**
     * Convertit le type + outcome en code universel lisible (A5 CDC v3.1).
     */
    public function toUniversalCode(string $betType, string $outcome): string
    {
        // 1X2 / Double Chance — outcome est déjà le code universel
        if (in_array($betType, ['1X2', 'Double Chance'])) {
            return $outcome;
        }

        // Over/Under buts
        if ($betType === 'Over/Under') {
            if (str_starts_with($outcome, 'Over')) {
                return 'O'.str_replace('Over ', ' ', $outcome);
            }
            if (str_starts_with($outcome, 'Under')) {
                return 'U'.str_replace('Under ', ' ', $outcome);
            }
        }

        // BTTS
        if ($betType === 'BTTS') {
            return $outcome === 'Oui' ? 'GG' : 'NG';
        }

        // Corners
        if ($betType === 'Corners') {
            return preg_replace('/Over (\d+\.?\d*) corners/', 'COR O $1', $outcome)
                ?? preg_replace('/Under (\d+\.?\d*) corners/', 'COR U $1', $outcome)
                ?? $outcome;
        }

        // Shots
        if ($betType === 'Shots') {
            return preg_replace('/Over (\d+\.?\d*) shots/', 'SOT O $1', $outcome)
                ?? preg_replace('/Under (\d+\.?\d*) shots/', 'SOT U $1', $outcome)
                ?? $outcome;
        }

        return $outcome;
    }

    /**
     * Détermine si un match est un tournoi international
     * d'après la structure fixture API-Football.
     */
    public function isTournament(array $fixture): bool
    {
        $leagueId = $fixture['league']['id'] ?? null;

        // IDs de compétitions internationales dans API-Football
        $internationalLeagues = [
            1,   // World Cup
            4,   // Euro
            6,   // Africa Cup of Nations
            7,   // Asia Cup
            8,   // Copa America
            9,   // CONCACAF Gold Cup
            17,  // AFCON Qualification
            29,  // CAF Champions League
            34,  // World Cup Qualification (Africa)
            35,  // World Cup Qualification (Asia)
        ];

        if ($leagueId && in_array($leagueId, $internationalLeagues)) {
            return true;
        }

        // Fallback sur le type de compétition si disponible
        $countryName = strtolower($fixture['league']['country'] ?? '');

        return in_array($countryName, ['world', 'africa', 'europe', 'asia', 'south america', 'north america']);
    }

    /**
     * Fallback si aucun candidat valide.
     */
    private function fallback(float $totalScore, string $homeName): array
    {
        return [
            'type' => 'Double Chance',
            'outcome' => '1X',
            'odds' => 1.55,
            'engine' => 'force',
            'market_value' => 0.0,
            'market_score' => round(min($totalScore, 100.0), 2),
            'score_tier' => $this->scoreTier(min($totalScore, 100.0)),
            'active_side' => 'home',
            'market_selection' => '1X',
        ];
    }
}
