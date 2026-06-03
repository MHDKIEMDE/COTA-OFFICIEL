<?php

namespace App\Services;

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
    private const DIVERSITY_MAX_SAME_COMP   = 2;
    private const DIVERSITY_MAX_SAME_MARKET = 2;

    /**
     * Point d'entrée — retourne les 3 variantes.
     *
     * @param  Collection $rows  Prédictions du jour (stdClass depuis DB::table)
     * @param  bool       $floorApplied  Si plancher actif (< 3 qualifiés gold+std)
     */
    public function buildAll(Collection $rows, bool $floorApplied = false): array
    {
        $cfg = config('cota.coupon');

        return [
            'prudent'   => $this->buildSafe($rows, $cfg['safe'], $floorApplied),
            'audacieux' => $this->buildBold($rows, $cfg['bold']),
            'equilibre' => $this->buildBalanced($rows),
        ];
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
        $pool = $rows->filter(fn($r) =>
            (float) ($r->total_score ?? 0) >= $minScore
            && (float) ($r->odds ?? 0) >= $cfg['odds_min']
            && (float) ($r->odds ?? 0) <= $cfg['odds_max']
        )->sortByDesc(fn($r) => (float) ($r->total_score ?? 0))->values();

        // Fallback : accepter toutes les cotes si pas assez dans la bande
        if ($pool->count() < 3) {
            $pool = $rows->filter(fn($r) =>
                (float) ($r->total_score ?? 0) >= $minScore
            )->sortByDesc(fn($r) => (float) ($r->total_score ?? 0))->values();
        }

        $selected = $this->selectWithDiversity($pool, $maxPicks);

        if (count($selected) < 3) return null;

        return $this->formatVariant($selected, 'Prudent', false, false);
    }

    // ── Coupon Audacieux (Premium) ───────────────────────────────────────────

    /**
     * Picks avec cote ≥ 2.50 parmi les mieux notés (~40% proba).
     * is_risky = true — affiché avec avertissement explicite côté mobile.
     */
    private function buildBold(Collection $rows, array $cfg): ?array
    {
        $pool = $rows->filter(fn($r) =>
            (float) ($r->odds ?? 0) >= $cfg['odds_min']
            && (float) ($r->total_score ?? 0) >= config('cota.tiers.bronze', 35.0)
        )->sortByDesc(fn($r) => (float) ($r->total_score ?? 0))->values();

        $selected = $this->selectWithDiversity($pool, $cfg['picks']);

        if (count($selected) < 3) return null;

        return $this->formatVariant($selected, 'Audacieux', true, true);
    }

    // ── Coupon Équilibré (Premium) ───────────────────────────────────────────

    /**
     * Meilleure combinaison globale — pas de contrainte de cote.
     * Cible gold+standard, sélection par market_score ou total_score.
     */
    private function buildBalanced(Collection $rows): ?array
    {
        $minScore = config('cota.tiers.standard', 50.0);

        $pool = $rows->filter(fn($r) =>
            (float) ($r->total_score ?? 0) >= $minScore
            && (float) ($r->odds ?? 0) >= 1.40
        )->sortByDesc(fn($r) =>
            (float) ($r->market_score ?? $r->total_score ?? 0)
        )->values();

        // Fallback léger
        if ($pool->count() < 3) {
            $pool = $rows->filter(fn($r) =>
                (float) ($r->total_score ?? 0) >= config('cota.tiers.bronze', 35.0)
            )->sortByDesc(fn($r) => (float) ($r->total_score ?? 0))->values();
        }

        $selected = $this->selectWithDiversity($pool, config('cota.coupon.safe.picks', 5));

        if (count($selected) < 3) return null;

        return $this->formatVariant($selected, 'Équilibré', true, false);
    }

    // ── Sélection avec diversité ─────────────────────────────────────────────

    private function selectWithDiversity(Collection $pool, int $maxPicks): array
    {
        $selected  = [];
        $compCount = [];
        $mktCount  = [];
        $usedMatch = [];

        foreach ($pool as $row) {
            if (count($selected) >= $maxPicks) break;

            $comp   = $row->competition ?? 'unknown';
            $market = $row->bet_type    ?? '1X2';
            $mid    = $row->match_id    ?? null;

            if (($compCount[$comp]   ?? 0) >= self::DIVERSITY_MAX_SAME_COMP)   continue;
            if (($mktCount[$market]  ?? 0) >= self::DIVERSITY_MAX_SAME_MARKET) continue;
            if ($mid && in_array($mid, $usedMatch, true))                       continue;
            // Exclure les picks contradicts si l'info est disponible
            $analysis = $row->analysis_details
                ? (json_decode($row->analysis_details, true) ?? [])
                : [];
            if (($analysis['third_party']['agreement'] ?? '') === 'contradicts') continue;

            $selected[]           = $row;
            $compCount[$comp]     = ($compCount[$comp]   ?? 0) + 1;
            $mktCount[$market]    = ($mktCount[$market]  ?? 0) + 1;
            if ($mid) $usedMatch[] = $mid;
        }

        return $selected;
    }

    // ── Formatage ────────────────────────────────────────────────────────────

    private function formatVariant(array $selected, string $label, bool $isPremium, bool $isRisky): array
    {
        $totalOdds     = array_reduce($selected, fn($c, $r) => $c * max((float)($r->odds ?? 1.0), 1.0), 1.0);
        $avgConfidence = array_sum(array_map(fn($r) => (float)($r->total_score ?? 0), $selected)) / count($selected);
        $potentialGain = (int) round($totalOdds * 1000);

        $picks = array_map(fn($r) => [
            'id'              => $r->id,
            'match_id'        => $r->match_id,
            'match'           => trim(($r->home_team ?? '?') . ' vs ' . ($r->away_team ?? '?')),
            'league'          => $r->competition ?? '',
            'league_logo'     => $r->competition_logo ?? null,
            'home_team_logo'  => $r->home_team_logo ?? null,
            'away_team_logo'  => $r->away_team_logo ?? null,
            'date'            => $r->match_date ?? null,
            'time'            => $r->match_time ?? null,
            'prediction'      => $r->prediction ?? '',
            'market_selection'=> $r->market_selection ?? $r->prediction ?? '',
            'type'            => $r->bet_type ?? '1X2',
            'active_side'     => $r->active_side ?? 'none',
            'odds'            => (float) ($r->odds ?? 1.0),
            'confidence'      => round((float)($r->total_score ?? 0), 1),
            'stars'           => (int) ($r->confidence_stars ?? 1),
            'score_tier'      => $r->score_tier ?? null,
            'is_premium'      => (bool) ($r->is_premium ?? false),
            'odds_source'     => $this->extractOddsSource($r),
            'is_confirmed_ia' => $this->isConfirmedByIa($r),
        ], $selected);

        return [
            'label'            => $label,
            'is_premium'       => $isPremium,
            'is_risky'         => $isRisky,
            'picks_count'      => count($selected),
            'picks'            => $picks,
            'total_odds'       => round($totalOdds, 2),
            'avg_confidence'   => round($avgConfidence, 1),
            'potential_gain_1000' => $potentialGain,
            'floor_applied'    => false,
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
