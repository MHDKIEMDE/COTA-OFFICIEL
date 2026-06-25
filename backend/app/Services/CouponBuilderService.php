<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * CouponBuilderService — T4 CDC v3.1
 *
 * Génère les 3 variantes de coupon depuis le pool de prédictions du jour :
 *   - Coupon Sûr   (Free)    : 5 picks haute confiance, cotes 1.20–1.80, total ~8–10
 *   - Coupon Audacieux (Premium) : 5 picks cote ≥ 2.50, is_risky = true
 *   - Coupon Équilibré (Premium) : 5 picks best global, cote totale 10–25
 *
 * Règle fondamentale A3 :
 *   Mieux vaut un petit coupon honnête qu'un gros coupon pourri.
 *   Le plancher de sécurité réduit à 3–4 picks si pas assez de qualifiés.
 */
class CouponBuilderService
{
    private const DIVERSITY_MAX_SAME_COMP = 2;

    // 1X2 est le marché dominant et légitime en combiné : on tolère jusqu'à 4 picks
    // du même marché, sinon les coupons ne sortent pas les jours où le 1X2 domine.
    private const DIVERSITY_MAX_SAME_MARKET = 4;

    /** Compétition de grande envergure : tier ≤ ce seuil → coupon dédié. */
    private const MAJOR_COMP_MAX_TIER = 2;

    /** Min de pronos dans une compétition pour générer son coupon dédié. */
    private const COMP_COUPON_MIN_PICKS = 3;

    /**
     * Point d'entrée — retourne les 3 variantes.
     *
     * @param  Collection  $rows  Prédictions du jour (stdClass depuis DB::table)
     * @param  bool  $floorApplied  Si plancher actif (< 3 qualifiés gold+std)
     */
    public function buildAll(Collection $rows, bool $floorApplied = false, ?Collection $majorRows = null): array
    {
        $cfg = config('cota.coupon');

        return [
            'prudent' => $this->buildSafe($rows, $cfg['safe'], $floorApplied),
            'equilibre' => $this->buildBalanced($rows),
            'kamikaze' => $this->buildKamikaze($rows, $cfg['kamikaze'] ?? []),
            // 'audacieux' remplacé par 'kamikaze' (même créneau haut risque).
            'audacieux' => null,
            'competitions' => $this->buildByCompetition($majorRows ?? $rows),
        ];
    }

    // ── Coupons par compétition de grande envergure ───────────────────────────

    /**
     * Génère, pour chaque compétition de grande envergure (tier ≤ MAJOR_COMP_MAX_TIER)
     * ayant au moins COMP_COUPON_MIN_PICKS pronos, ses 3 variantes de coupon
     * (Prudent / Équilibré / Audacieux) — comme le coupon global, mais limité
     * aux matchs de cette seule compétition (J et J+1).
     *
     * Objectif produit : l'app est centrée sur les coupons. Dès qu'une compétition
     * vedette (Coupe du monde, Champions League…) a ≥3 pronos, on en sort un
     * combiné dédié, en combinant si besoin les matchs de J et J+1.
     *
     * @return array<int, array{competition:string, prudent:?array, equilibre:?array, audacieux:?array}>
     */
    private function buildByCompetition(Collection $rows): array
    {
        $byComp = $rows
            ->filter(fn ($r) => $this->isFeaturedCompetition($r))
            ->groupBy(fn ($r) => $r->competition ?? 'Unknown');

        $coupons = [];
        $cfg = config('cota.coupon');

        foreach ($byComp as $competition => $compRows) {
            // Au moins 3 pronos dans la compétition (J + J+1 confondus) sinon on passe.
            if ($compRows->count() < self::COMP_COUPON_MIN_PICKS) {
                continue;
            }

            // Un coupon PAR JOUR, taille DYNAMIQUE (n-1 matchs du jour, min 3).
            // Si un jour a < 3 matchs, on COMPLÈTE avec les matchs des jours
            // suivants (report) pour ne jamais perdre un coupon.
            $days = $this->buildDailyCoupons($compRows);

            if (empty($days)) {
                continue;
            }

            $coupons[] = [
                'competition' => $competition,
                'is_competition_coupon' => true,
                'days' => $days,
            ];
        }

        return $coupons;
    }

    /**
     * Vrai si la compétition de cette prédiction est une « vedette » :
     * soit son nom matche la liste cota.coupon.featured_competitions (grands
     * tournois internationaux : Mondial, Euro, CAN, Copa, LDC…), soit elle est
     * taguée tier ≤ MAJOR_COMP_MAX_TIER (compatibilité grandes coupes UEFA).
     * → L'onglet vedette du coupon devient dynamique : il suit la compétition
     *   en cours et disparaît quand elle se termine.
     */
    private function isFeaturedCompetition(object $r): bool
    {
        if ((int) ($r->league_tier ?? 99) <= self::MAJOR_COMP_MAX_TIER) {
            return true;
        }

        $name = strtolower((string) ($r->competition ?? ''));
        if ($name === '') {
            return false;
        }

        foreach (config('cota.coupon.featured_competitions', []) as $pattern) {
            if (str_contains($name, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Génère un coupon par jour, taille dynamique :
     *  - n matchs ce jour → coupon de n-1 picks (on retire le moins fiable), min 3
     *  - moins de 3 matchs un jour → on emprunte aux jours suivants jusqu'à 3
     *
     * @return array<int, array>
     */
    private function buildDailyCoupons(Collection $compRows): array
    {
        // Index des jours triés (chaque jour = matchs triés par confiance desc).
        $byDay = $compRows
            ->groupBy(fn ($r) => Carbon::parse($r->match_date)->format('Y-m-d'))
            ->map(fn ($g) => $g->sortByDesc(fn ($r) => (float) ($r->total_score ?? 0))->values())
            ->sortKeys();

        $dayKeys = $byDay->keys()->all();
        $days = [];

        foreach ($dayKeys as $i => $day) {
            $own = $byDay[$day];

            // Taille cible = n-1 (presque tous), bornée par le max de picks coupon.
            $target = min(max($own->count() - 1, self::COMP_COUPON_MIN_PICKS), config('cota.coupon.safe.picks', 5));

            $selected = $this->selectForCompetition($own, $target);

            // Pas assez ce jour → emprunter aux jours SUIVANTS (report).
            if (count($selected) < self::COMP_COUPON_MIN_PICKS) {
                $usedIds = array_map(fn ($r) => $r->match_id ?? null, $selected);
                for ($j = $i + 1; $j < count($dayKeys) && count($selected) < self::COMP_COUPON_MIN_PICKS; $j++) {
                    foreach ($byDay[$dayKeys[$j]] as $row) {
                        if (count($selected) >= self::COMP_COUPON_MIN_PICKS) {
                            break;
                        }
                        if (in_array($row->match_id ?? null, $usedIds, true)) {
                            continue;
                        }
                        $selected[] = $row;
                        $usedIds[] = $row->match_id ?? null;
                    }
                }
            }

            if (count($selected) < self::COMP_COUPON_MIN_PICKS) {
                continue; // même avec report, pas assez de matchs au total
            }

            $date = Carbon::parse($day);
            $variant = $this->formatVariant($selected, 'Coupon '.$this->frenchDayLabel($date), false, false);
            $variant['day'] = $day;
            $variant['day_label'] = $this->frenchDayLabel($date);
            $days[] = $variant;
        }

        return $days;
    }

    /** Libellé jour FR court (ex. "23 juin"). */
    private function frenchDayLabel(Carbon $date): string
    {
        $mois = [1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.', 5 => 'mai', 6 => 'juin',
            7 => 'juil.', 8 => 'août', 9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.'];

        return $date->day.' '.($mois[$date->month] ?? '');
    }

    /**
     * Sélection pour un coupon mono-compétition : diversité par marché et par
     * match uniquement (la contrainte "même compétition" n'a pas de sens ici).
     */
    private function selectForCompetition(Collection $pool, int $maxPicks): array
    {
        $selected = [];
        $mktCount = [];
        $usedMatch = [];

        foreach ($pool as $row) {
            if (count($selected) >= $maxPicks) {
                break;
            }

            $market = $row->bet_type ?? '1X2';
            $mid = $row->match_id ?? null;

            if (($mktCount[$market] ?? 0) >= self::DIVERSITY_MAX_SAME_MARKET) {
                continue;
            }
            if ($mid && in_array($mid, $usedMatch, true)) {
                continue;
            }

            $analysis = $row->analysis_details
                ? (json_decode($row->analysis_details, true) ?? [])
                : [];
            if (($analysis['third_party']['agreement'] ?? '') === 'contradicts') {
                continue;
            }

            $selected[] = $row;
            $mktCount[$market] = ($mktCount[$market] ?? 0) + 1;
            if ($mid) {
                $usedMatch[] = $mid;
            }
        }

        return $selected;
    }

    // ── Coupon Sûr (Free) ────────────────────────────────────────────────────

    /**
     * Picks gold+standard, cotes 1.20–1.80, cote totale ~8–10.
     * Si plancher actif → 3–4 picks max, pas de bronze.
     */
    private function buildSafe(Collection $rows, array $cfg, bool $floorApplied): ?array
    {
        $maxPicks = $floorApplied
            ? config('cota.safety_floor.reduced_free_max', 4)
            : $cfg['picks'];

        $minScore = config('cota.tiers.standard', 50.0);

        // Pool : gold + standard uniquement, dans la bande de cote sûre
        $pool = $rows->filter(fn ($r) => (float) ($r->total_score ?? 0) >= $minScore
            && (float) ($r->odds ?? 0) >= $cfg['odds_min']
            && (float) ($r->odds ?? 0) <= $cfg['odds_max']
        )->sortByDesc(fn ($r) => (float) ($r->total_score ?? 0))->values();

        // Fallback : accepter toutes les cotes si pas assez dans la bande
        if ($pool->count() < 3) {
            $pool = $rows->filter(fn ($r) => (float) ($r->total_score ?? 0) >= $minScore
            )->sortByDesc(fn ($r) => (float) ($r->total_score ?? 0))->values();
        }

        $selected = $this->selectWithDiversity($pool, $maxPicks);

        if (count($selected) < 3) {
            return null;
        }

        return $this->formatVariant($selected, 'Prudent', false, false);
    }

    // ── Coupon Audacieux (Premium) ───────────────────────────────────────────

    /**
     * Picks avec cote ≥ 2.50 parmi les mieux notés (~40% proba).
     * is_risky = true — affiché avec avertissement explicite côté mobile.
     */
    private function buildBold(Collection $rows, array $cfg): ?array
    {
        $pool = $rows->filter(fn ($r) => (float) ($r->odds ?? 0) >= $cfg['odds_min']
            && (float) ($r->total_score ?? 0) >= config('cota.tiers.bronze', 35.0)
        )->sortByDesc(fn ($r) => (float) ($r->total_score ?? 0))->values();

        $selected = $this->selectWithDiversity($pool, $cfg['picks']);

        if (count($selected) < 3) {
            return null;
        }

        return $this->formatVariant($selected, 'Audacieux', true, true);
    }

    // ── Coupon Kamikaze (Premium) ─────────────────────────────────────────────

    /**
     * Combiné très haut risque : on empile le MAXIMUM de picks à cote élevée
     * pour viser une cote totale énorme (gain potentiel maximal, proba faible).
     * Diversité assouplie (on cherche le volume) ; toujours is_risky = true.
     */
    private function buildKamikaze(Collection $rows, array $cfg): ?array
    {
        $oddsMin = (float) ($cfg['odds_min'] ?? 1.80);
        $maxPicks = (int) ($cfg['picks'] ?? 8);
        $totalMin = (float) ($cfg['total_min'] ?? 30.0);

        $pool = $rows->filter(fn ($r) => (float) ($r->odds ?? 0) >= $oddsMin
            && (float) ($r->total_score ?? 0) >= config('cota.tiers.bronze', 35.0)
        )->sortByDesc(fn ($r) => (float) ($r->odds ?? 0)) // priorité à la cote (volume)
            ->values();

        // Sélection volume : 1 pick max par match, marché/compétition libres.
        $selected = [];
        $usedMatch = [];
        foreach ($pool as $row) {
            if (count($selected) >= $maxPicks) {
                break;
            }
            $mid = $row->match_id ?? null;
            if ($mid && in_array($mid, $usedMatch, true)) {
                continue;
            }
            $selected[] = $row;
            if ($mid) {
                $usedMatch[] = $mid;
            }
        }

        if (count($selected) < 4) {
            return null;
        } // kamikaze = combiné, min 4 picks

        $coupon = $this->formatVariant($selected, 'Kamikaze', true, true);

        // Fallback « jamais vide » : si la cote totale n'atteint pas la cible grosse
        // cote, on sort quand même le meilleur combiné du jour, marqué below_target
        // pour que le mobile l'affiche comme « cote du jour » plutôt qu'un vrai kamikaze.
        $coupon['below_target'] = ($coupon['total_odds'] ?? 0) < $totalMin;
        $coupon['target_odds'] = $totalMin;

        return $coupon;
    }

    // ── Coupon Équilibré (Premium) ───────────────────────────────────────────

    /**
     * Meilleure combinaison globale — pas de contrainte de cote.
     * Cible gold+standard, sélection par market_score ou total_score.
     */
    private function buildBalanced(Collection $rows): ?array
    {
        $minScore = config('cota.tiers.standard', 50.0);

        $pool = $rows->filter(fn ($r) => (float) ($r->total_score ?? 0) >= $minScore
            && (float) ($r->odds ?? 0) >= 1.40
        )->sortByDesc(fn ($r) => (float) ($r->market_score ?? $r->total_score ?? 0)
        )->values();

        // Fallback léger
        if ($pool->count() < 3) {
            $pool = $rows->filter(fn ($r) => (float) ($r->total_score ?? 0) >= config('cota.tiers.bronze', 35.0)
            )->sortByDesc(fn ($r) => (float) ($r->total_score ?? 0))->values();
        }

        $selected = $this->selectWithDiversity($pool, config('cota.coupon.safe.picks', 5));

        if (count($selected) < 3) {
            return null;
        }

        return $this->formatVariant($selected, 'Équilibré', true, false);
    }

    // ── Sélection avec diversité ─────────────────────────────────────────────

    private function selectWithDiversity(Collection $pool, int $maxPicks): array
    {
        $selected = [];
        $compCount = [];
        $mktCount = [];
        $usedMatch = [];

        foreach ($pool as $row) {
            if (count($selected) >= $maxPicks) {
                break;
            }

            $comp = $row->competition ?? 'unknown';
            $market = $row->bet_type ?? '1X2';
            $mid = $row->match_id ?? null;

            if (($compCount[$comp] ?? 0) >= self::DIVERSITY_MAX_SAME_COMP) {
                continue;
            }
            if (($mktCount[$market] ?? 0) >= self::DIVERSITY_MAX_SAME_MARKET) {
                continue;
            }
            if ($mid && in_array($mid, $usedMatch, true)) {
                continue;
            }
            // Exclure les picks contradicts si l'info est disponible
            $analysis = $row->analysis_details
                ? (json_decode($row->analysis_details, true) ?? [])
                : [];
            if (($analysis['third_party']['agreement'] ?? '') === 'contradicts') {
                continue;
            }

            $selected[] = $row;
            $compCount[$comp] = ($compCount[$comp] ?? 0) + 1;
            $mktCount[$market] = ($mktCount[$market] ?? 0) + 1;
            if ($mid) {
                $usedMatch[] = $mid;
            }
        }

        return $selected;
    }

    // ── Diversité des marchés ────────────────────────────────────────────────

    /**
     * Substitue le marché de certains picks par un marché alternatif issu de
     * prediction_markets, pour éviter un coupon "100% victoires".
     *
     * - Charge tous les marchés des picks en une seule requête (pas de N+1)
     * - Variante audacieuse : privilégie les marchés risqués (cotes élevées)
     * - Variantes sûre/équilibrée : diversifie vers d'autres catégories fiables
     *   quand un même marché revient trop souvent
     *
     * Ne modifie jamais l'objet pick d'origine (clone) pour rester sans effet de bord.
     */
    private function applyMarketVariety(array $selected, bool $isRisky): array
    {
        $ids = array_filter(array_map(fn ($r) => $r->id ?? null, $selected));
        if (empty($ids)) {
            return $selected;
        }

        $byPrediction = \App\Models\PredictionMarket::whereIn('prediction_id', $ids)
            ->get()
            ->groupBy('prediction_id');

        $seenCategory = [];

        foreach ($selected as $i => $pick) {
            $markets = $byPrediction->get($pick->id ?? 0);
            if (! $markets || $markets->count() <= 1) {
                continue;
            }

            // Pool de substitution selon la variante.
            // Coupons sûr/équilibré : jamais de marché risqué ni haute-variance (Score Exact).
            $pool = $isRisky
                ? $markets->where('is_risky', true)
                : $markets->whereIn('score_tier', ['gold', 'standard'])
                    ->where('is_risky', false)
                    ->where('engine', '!=', 'high_variance');

            if ($pool->isEmpty()) {
                $pool = $markets->where('is_risky', false)->where('engine', '!=', 'high_variance');
            }

            // Catégorie du marché principal actuel
            $currentCat = \App\Services\MarketScoringService::CATEGORY_MAP[$pick->bet_type ?? '1X2'] ?? 'resultat';

            // Si cette catégorie est déjà présente dans le coupon, on tente une autre catégorie
            $needsSwitch = $isRisky || in_array($currentCat, $seenCategory, true);

            if ($needsSwitch) {
                $alt = $pool->whereNotIn('category', $seenCategory)->sortByDesc('market_score')->first()
                    ?? $pool->sortByDesc('market_score')->first();

                if ($alt) {
                    $selected[$i] = $this->overrideMarket($pick, $alt);
                    $seenCategory[] = $alt->category;

                    continue;
                }
            }

            $seenCategory[] = $currentCat;
        }

        return $selected;
    }

    /**
     * Clone un pick et y applique les champs d'un marché alternatif.
     */
    private function overrideMarket(object $pick, \App\Models\PredictionMarket $market): object
    {
        $clone = clone $pick;
        $clone->prediction = $market->outcome;
        $clone->market_selection = $market->market_selection ?? $market->outcome;
        $clone->bet_type = $market->bet_type;
        $clone->active_side = $market->active_side;
        $clone->odds = (float) $market->odds;
        $clone->score_tier = $market->score_tier;

        return $clone;
    }

    // ── Formatage ────────────────────────────────────────────────────────────

    private function formatVariant(array $selected, string $label, bool $isPremium, bool $isRisky): array
    {
        // Diversifier les marchés : casser la monotonie "que des victoires"
        // en substituant certains picks par un marché alternatif fiable.
        $selected = $this->applyMarketVariety($selected, $isRisky);

        $totalOdds = array_reduce($selected, fn ($c, $r) => $c * max((float) ($r->odds ?? 1.0), 1.0), 1.0);
        $avgConfidence = array_sum(array_map(fn ($r) => (float) ($r->total_score ?? 0), $selected)) / count($selected);
        $potentialGain = (int) round($totalOdds * 1000);

        $picks = array_map(fn ($r) => [
            'id' => $r->id,
            'match_id' => $r->match_id,
            'match' => trim(($r->home_team ?? '?').' vs '.($r->away_team ?? '?')),
            'home_team' => $r->home_team ?? '',
            'away_team' => $r->away_team ?? '',
            'league' => $r->competition ?? '',
            'league_logo' => $r->competition_logo ?? null,
            'home_team_logo' => $r->home_team_logo ?? null,
            'away_team_logo' => $r->away_team_logo ?? null,
            'date' => $r->match_date ?? null,
            'time' => $r->match_time ?? null,
            'prediction' => $r->prediction ?? '',
            'market_selection' => $r->market_selection ?? $r->prediction ?? '',
            'type' => $r->bet_type ?? '1X2',
            'active_side' => $r->active_side ?? 'none',
            'odds' => (float) ($r->odds ?? 1.0),
            'confidence' => round((float) ($r->total_score ?? 0), 1),
            'stars' => (int) ($r->confidence_stars ?? 1),
            'score_tier' => $r->score_tier ?? null,
            'is_premium' => (bool) ($r->is_premium ?? false),
            'odds_source' => $this->extractOddsSource($r),
            'is_confirmed_ia' => $this->isConfirmedByIa($r),
        ], $selected);

        return [
            'label' => $label,
            'is_premium' => $isPremium,
            'is_risky' => $isRisky,
            'picks_count' => count($selected),
            'picks' => $picks,
            'total_odds' => round($totalOdds, 2),
            'avg_confidence' => round($avgConfidence, 1),
            'potential_gain_1000' => $potentialGain,
            'floor_applied' => false,
        ];
    }

    private function extractOddsSource(object $r): string
    {
        $details = $r->analysis_details
            ? (json_decode($r->analysis_details, true) ?? [])
            : [];

        return $details['odds_source'] ?? 'estimated';
    }

    private function isConfirmedByIa(object $r): bool
    {
        $details = $r->analysis_details
            ? (json_decode($r->analysis_details, true) ?? [])
            : [];
        $agreement = $details['third_party']['agreement'] ?? '';

        return in_array($agreement, ['confirms', 'partial'], true);
    }
}
