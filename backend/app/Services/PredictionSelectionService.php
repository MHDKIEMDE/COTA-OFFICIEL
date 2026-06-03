<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * PredictionSelectionService — T3 CDC v3.1
 *
 * Pipeline quotidien A3 :
 * 1. Jeter < bronze (< 35)
 * 2. Trier par score décroissant
 * 3. Remplir pool Free (gold+standard ≥50, 4–6 picks)
 * 4. Remplir pool Premium (gold+standard+bronze ≥35, 12–18 picks)
 * 5. Appliquer règles de diversité
 * 6. Appliquer plancher de sécurité
 * 7. Mode largeur/profondeur adaptatif
 */
class PredictionSelectionService
{
    // Familles de marchés pour la règle diversité coupon free
    private const MARKET_FAMILIES = [
        '1x2'          => ['1X2'],
        'double_chance' => ['Double Chance'],
        'goals'         => ['Over/Under', 'BTTS', 'Team Goals'],
        'corners'       => ['Corners'],
        'cards'         => ['Cards'],
        'shots'         => ['Shots'],
    ];

    /**
     * Point d'entrée principal.
     *
     * @param  array $predictions  Liste de tableaux prediction_data issus de generatePredictionForFixture()
     * @return array{free: array, premium: array, depth_mode: bool, floor_applied: bool}
     */
    public function buildPools(array $predictions): array
    {
        $cfg     = config('cota');
        $freeCfg = $cfg['pools']['free'];
        $premCfg = $cfg['pools']['premium'];
        $floor   = $cfg['safety_floor'];
        $div     = $cfg['diversity'];

        // ── 1. Filtrer et trier ──────────────────────────────────────────────
        $qualified = collect($predictions)
            ->filter(fn($p) => $this->getScore($p) >= $cfg['tiers']['bronze'])
            ->sortByDesc(fn($p) => $this->getScore($p))
            ->values();

        Log::info('PredictionSelectionService: qualifiés', [
            'total'     => count($predictions),
            'qualified' => $qualified->count(),
        ]);

        // ── 2. Détection mode largeur / profondeur ───────────────────────────
        $depthMode = $qualified->count() <= $cfg['depth_mode_threshold'];

        // ── 3. Plancher de sécurité ──────────────────────────────────────────
        $qualifiedFree = $qualified->filter(fn($p) => $this->getScore($p) >= $freeCfg['min_score']);
        $floorApplied  = $qualifiedFree->count() < $floor['min_qualified'];

        $freeMax = $floorApplied
            ? $floor['reduced_free_max']
            : $freeCfg['max'];

        // ── 4. Pool Free (gold + standard uniquement) ────────────────────────
        $freePool = $this->fillPool(
            source:   $qualifiedFree,
            min:      $freeCfg['min'],
            max:      $freeMax,
            maxSameMarket: $div['max_same_market'],
            maxSameComp:   $div['max_same_competition'],
        );

        // ── 5. Pool Premium (gold + standard + bronze) ───────────────────────
        $premiumPool = $this->fillPool(
            source:   $qualified,
            min:      $premCfg['min'],
            max:      $premCfg['max'],
            maxSameMarket: $div['max_same_market'] + 1, // premium moins restrictif
            maxSameComp:   $div['max_same_competition'] + 1,
        );

        // ── 6. Valider diversité familles coupon free ────────────────────────
        $families = $this->countFamilies($freePool);
        if ($families < $div['min_market_families'] && !$floorApplied) {
            Log::info('PredictionSelectionService: diversité insuffisante, injection', [
                'familles' => $families,
                'requis'   => $div['min_market_families'],
            ]);
            $freePool = $this->injectDiversity($freePool, $qualified, $div['min_market_families'], $freeMax);
        }

        Log::info('PredictionSelectionService: pools construits', [
            'free'         => count($freePool),
            'premium'      => count($premiumPool),
            'depth_mode'   => $depthMode,
            'floor_applied'=> $floorApplied,
        ]);

        return [
            'free'          => $freePool,
            'premium'       => $premiumPool,
            'depth_mode'    => $depthMode,
            'floor_applied' => $floorApplied,
        ];
    }

    /**
     * Remplir un pool en respectant diversité marché + compétition.
     */
    private function fillPool(
        Collection $source,
        int        $min,
        int        $max,
        int        $maxSameMarket,
        int        $maxSameComp
    ): array {
        $selected      = [];
        $marketCount   = [];
        $compCount     = [];

        foreach ($source as $p) {
            if (count($selected) >= $max) break;

            $market = $this->getMarket($p);
            $comp   = $this->getCompetition($p);

            // Règle diversité marché
            if (($marketCount[$market] ?? 0) >= $maxSameMarket) continue;
            // Règle diversité compétition
            if (($compCount[$comp] ?? 0) >= $maxSameComp) continue;

            $selected[]           = $p;
            $marketCount[$market] = ($marketCount[$market] ?? 0) + 1;
            $compCount[$comp]     = ($compCount[$comp] ?? 0) + 1;
        }

        return $selected;
    }

    /**
     * Injecter des picks de familles manquantes pour atteindre min_market_families.
     */
    private function injectDiversity(array $current, Collection $source, int $minFamilies, int $max): array
    {
        $presentFamilies = $this->getFamiliesInPool($current);

        foreach ($source as $p) {
            if (count($current) >= $max) break;
            if ($this->countFamilies($current) >= $minFamilies) break;

            $family = $this->getFamily($this->getMarket($p));
            if (!in_array($family, $presentFamilies)) {
                // Éviter doublon
                $alreadyIn = array_filter($current, fn($c) => $this->getId($c) === $this->getId($p));
                if (empty($alreadyIn)) {
                    $current[]         = $p;
                    $presentFamilies[] = $family;
                }
            }
        }

        return $current;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getScore(mixed $p): float
    {
        // Supporte array (prediction_data) ou Prediction model
        if (is_array($p)) return (float) ($p['market_score'] ?? $p['confidence'] ?? $p['total_score'] ?? 0);
        return (float) ($p->market_score ?? $p->total_score ?? 0);
    }

    private function getMarket(mixed $p): string
    {
        if (is_array($p)) return $p['type'] ?? $p['bet_type'] ?? '1X2';
        return $p->bet_type ?? '1X2';
    }

    private function getCompetition(mixed $p): string
    {
        if (is_array($p)) return $p['competition'] ?? 'unknown';
        return $p->competition ?? 'unknown';
    }

    private function getId(mixed $p): mixed
    {
        if (is_array($p)) return $p['match_id'] ?? $p['fixture_id'] ?? null;
        return $p->id ?? null;
    }

    private function getFamily(string $market): string
    {
        foreach (self::MARKET_FAMILIES as $family => $markets) {
            if (in_array($market, $markets)) return $family;
        }
        return 'other';
    }

    private function countFamilies(array $pool): int
    {
        return count(array_unique(array_map(
            fn($p) => $this->getFamily($this->getMarket($p)),
            $pool
        )));
    }

    private function getFamiliesInPool(array $pool): array
    {
        return array_unique(array_map(
            fn($p) => $this->getFamily($this->getMarket($p)),
            $pool
        ));
    }
}
