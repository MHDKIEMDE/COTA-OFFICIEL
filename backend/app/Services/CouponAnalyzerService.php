<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * CouponAnalyzerService — Analyseur Intelligent de Coupons (feature Premium).
 *
 * Implémente le CŒUR DÉTERMINISTE du cadrage (doc COTA_Analyseur_Coupons_Cadrage.md) :
 * la couche 2 « Analyse ». Aucune IA ici — règles fixes uniquement.
 * La lecture de capture (couche 1) et l'habillage en français (couche 3)
 * sont hors de ce service.
 *
 * Principe (§5) : on découpe le coupon match par match et, pour chaque pick,
 * on le confronte à la prédiction COTA correspondante :
 *   - Vert   : le pick va dans le sens de COTA              → on confirme
 *   - Orange : choix neutre / non couvert, pas opposé       → on signale
 *   - Rouge  : le pick va CONTRE la prédiction COTA         → maillon dangereux
 *
 * Le verdict agrège ces findings, calcule un score de risque déterministe
 * (§4, p = 1/cote) et propose une version sécurisée réalignée sur COTA (§7).
 */
class CouponAnalyzerService
{
    /** Cote au-delà de laquelle un pick est signalé comme risqué (§4). */
    private const HIGH_ODDS_FLAG = 2.50;

    /** Score de confiance COTA sous lequel un pick est « statistiquement faible » (§4). */
    private const WEAK_CONFIDENCE = 50.0;

    /**
     * Analyser un coupon utilisateur en le croisant avec les prédictions COTA du jour.
     *
     * @param  array      $userPicks  Sélections lues du coupon. Chaque pick :
     *                                {home, away, bet_type, selection, odds}
     * @param  Collection $cotaRows   Prédictions COTA du jour (stdClass DB::table),
     *                                mêmes colonnes que CouponBuilderService.
     * @return array  Verdict structuré (verdict_json du cadrage §8).
     */
    public function analyze(array $userPicks, Collection $cotaRows): array
    {
        $findings = [];

        foreach ($userPicks as $pick) {
            $findings[] = $this->crossPick($pick, $cotaRows);
        }

        $combinedOdds = array_reduce(
            $userPicks,
            fn ($carry, $p) => $carry * max((float) ($p['odds'] ?? 1.0), 1.0),
            1.0
        );

        $combinedProb = $this->combinedProbability($userPicks);
        $riskLevel    = $this->classifyRisk($combinedProb, $findings);
        $secured      = $this->buildSecuredCoupon($findings);

        return [
            'verdict' => [
                'risk_level'      => $riskLevel,
                'combined_odds'   => round($combinedOdds, 2),
                'combined_prob'   => round($combinedProb * 100, 1),
                'green'           => $this->countByVerdict($findings, 'green'),
                'orange'          => $this->countByVerdict($findings, 'orange'),
                'red'             => $this->countByVerdict($findings, 'red'),
                'picks_count'     => count($userPicks),
            ],
            'findings'        => $findings,
            'secured_coupon'  => $secured,
        ];
    }

    /**
     * Confronte un pick utilisateur à la prédiction COTA du même match (§5).
     *
     * @return array{match:string,user_selection:string,bet_type:string,odds:float,verdict:string,severity:string,reason:string,cota_prediction:?string,suggestion:?string}
     */
    private function crossPick(array $pick, Collection $cotaRows): array
    {
        $home = (string) ($pick['home'] ?? '');
        $away = (string) ($pick['away'] ?? '');
        $odds = (float) ($pick['odds'] ?? 0.0);
        $userSel  = (string) ($pick['selection'] ?? '');
        $userType = (string) ($pick['bet_type'] ?? '1X2');

        $cota = $this->findCotaPrediction($home, $away, $cotaRows);

        // Aucune prédiction COTA sur ce match → orange (non couvert, pas opposé).
        if ($cota === null) {
            return $this->finding(
                $pick, 'orange', 'info',
                'Aucune prédiction COTA sur ce match : on ne peut pas confirmer.',
                null, null
            );
        }

        $cotaSense = $this->senseOf((string) ($cota->prediction ?? ''), (string) ($cota->bet_type ?? ''));
        $userSense = $this->senseOf($userSel, $userType);
        $cotaLabel = trim((string) ($cota->prediction ?? ''));

        // Sens opposés explicites → rouge (maillon dangereux).
        if ($cotaSense !== null && $userSense !== null && $this->areOpposite($userSense, $cotaSense)) {
            return $this->finding(
                $pick, 'red', 'critical',
                "Tu joues contre la prédiction COTA ($cotaLabel).",
                $cotaLabel,
                $cotaLabel
            );
        }

        // Même sens → vert (on confirme).
        if ($cotaSense !== null && $userSense === $cotaSense) {
            $weak = (float) ($cota->total_score ?? 0) < self::WEAK_CONFIDENCE;
            return $this->finding(
                $pick,
                $weak ? 'orange' : 'green',
                $weak ? 'medium' : 'info',
                $weak
                    ? "Même sens que COTA mais confiance faible (".(int) ($cota->total_score ?? 0)."/100)."
                    : "Va dans le sens de la prédiction COTA ($cotaLabel).",
                $cotaLabel,
                null
            );
        }

        // Marché différent / non comparable (BTTS, Over…) mais pas opposé → orange.
        $severity = $odds >= self::HIGH_ODDS_FLAG ? 'medium' : 'info';
        return $this->finding(
            $pick, 'orange', $severity,
            "Choix sur un marché non couvert par COTA : neutre, à surveiller.",
            $cotaLabel ?: null,
            null
        );
    }

    /**
     * Version sécurisée (§7) : on remplace chaque maillon rouge par la lecture COTA,
     * on garde le reste. Sens unique — vers MOINS de risque, jamais l'inverse.
     */
    private function buildSecuredCoupon(array $findings): array
    {
        $picks   = [];
        $changes = 0;

        foreach ($findings as $f) {
            if ($f['verdict'] === 'red' && $f['suggestion'] !== null) {
                $picks[] = [
                    'match'     => $f['match'],
                    'selection' => $f['suggestion'],
                    'replaced'  => true,
                    'was'       => $f['user_selection'],
                ];
                $changes++;
                continue;
            }

            $picks[] = [
                'match'     => $f['match'],
                'selection' => $f['user_selection'],
                'replaced'  => false,
                'was'       => null,
            ];
        }

        return [
            'picks'           => $picks,
            'changes'         => $changes,
            'is_safer'        => $changes > 0,
        ];
    }

    // ── Recherche de la prédiction COTA correspondante ────────────────────────

    private function findCotaPrediction(string $home, string $away, Collection $rows): ?object
    {
        $h = $this->norm($home);
        $a = $this->norm($away);

        foreach ($rows as $row) {
            $rh = $this->norm((string) ($row->home_team ?? ''));
            $ra = $this->norm((string) ($row->away_team ?? ''));

            $homeMatch = $rh !== '' && (str_contains($rh, $h) || str_contains($h, $rh));
            $awayMatch = $ra !== '' && (str_contains($ra, $a) || str_contains($a, $ra));

            if ($homeMatch && $awayMatch) {
                return $row;
            }
        }

        return null;
    }

    // ── Logique de sens (1 / X / 2 / double chance) ───────────────────────────

    /**
     * Réduit une sélection à un sens canonique : '1', 'X', '2', '1X', 'X2', '12'
     * ou null si marché non comparable (BTTS, Over/Under…).
     */
    private function senseOf(string $selection, string $betType): ?string
    {
        $s = $this->norm($selection);
        $t = $this->norm($betType);

        // Marchés hors 1X2/Double Chance → non comparables sur l'axe résultat.
        if (str_contains($t, 'btts') || str_contains($t, 'both') ||
            str_contains($t, 'over') || str_contains($t, 'under') ||
            str_contains($s, 'btts') || str_contains($s, 'over') || str_contains($s, 'under')) {
            return null;
        }

        // Double chance.
        if (str_contains($s, ' ou nul') || str_contains($s, 'ou nul')) return '1X';
        if (str_contains($s, 'nul ou')) return 'X2';
        if (str_contains($t, 'double') && !str_contains($s, 'nul')) return '12';

        // Résultat sec.
        if ($s === 'x' || str_contains($s, 'nul') || str_contains($s, 'draw')) return 'X';
        if (str_contains($s, 'domicile') || str_contains($s, 'home') || $s === '1') return '1';
        if (str_contains($s, 'exterieur') || str_contains($s, 'away') || $s === '2') return '2';

        return null;
    }

    /**
     * Deux sens sont opposés si l'un exclut totalement l'autre.
     * Ex. '1' vs '2', '1' vs 'X2', 'X' vs '12'. Tout chevauchement = non opposé.
     */
    private function areOpposite(string $userSense, string $cotaSense): bool
    {
        $covers = fn (string $sense): array => match ($sense) {
            '1'  => ['1'],
            'X'  => ['X'],
            '2'  => ['2'],
            '1X' => ['1', 'X'],
            'X2' => ['X', '2'],
            '12' => ['1', '2'],
            default => [],
        };

        $u = $covers($userSense);
        $c = $covers($cotaSense);

        if ($u === [] || $c === []) {
            return false;
        }

        return array_intersect($u, $c) === [];
    }

    // ── Risque déterministe (§4) ──────────────────────────────────────────────

    /** Probabilité combinée = produit des p = 1/cote de chaque pick. */
    private function combinedProbability(array $userPicks): float
    {
        return array_reduce($userPicks, function ($carry, $p) {
            $odds = (float) ($p['odds'] ?? 0.0);
            $prob = $odds > 0 ? 1.0 / $odds : 0.0;
            return $carry * $prob;
        }, 1.0);
    }

    /**
     * Classement Faible → Très élevé. La présence de maillons rouges remonte
     * mécaniquement le risque d'un cran.
     */
    private function classifyRisk(float $combinedProb, array $findings): string
    {
        $hasRed = $this->countByVerdict($findings, 'red') > 0;

        $base = match (true) {
            $combinedProb >= 0.50 => 'faible',
            $combinedProb >= 0.30 => 'modere',
            $combinedProb >= 0.15 => 'eleve',
            default               => 'tres_eleve',
        };

        if (!$hasRed) {
            return $base;
        }

        return match ($base) {
            'faible' => 'modere',
            'modere' => 'eleve',
            default  => 'tres_eleve',
        };
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function finding(
        array $pick,
        string $verdict,
        string $severity,
        string $reason,
        ?string $cotaPrediction,
        ?string $suggestion
    ): array {
        return [
            'match'           => trim(($pick['home'] ?? '?') . ' vs ' . ($pick['away'] ?? '?')),
            'user_selection'  => (string) ($pick['selection'] ?? ''),
            'bet_type'        => (string) ($pick['bet_type'] ?? '1X2'),
            'odds'            => (float) ($pick['odds'] ?? 0.0),
            'verdict'         => $verdict,
            'severity'        => $severity,
            'reason'          => $reason,
            'cota_prediction' => $cotaPrediction,
            'suggestion'      => $suggestion,
        ];
    }

    private function countByVerdict(array $findings, string $verdict): int
    {
        return count(array_filter($findings, fn ($f) => $f['verdict'] === $verdict));
    }

    /** Normalise un libellé pour comparaison : minuscules, sans accents, trim. */
    private function norm(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'û' => 'u', 'ù' => 'u', 'ü' => 'u',
            'ç' => 'c',
        ]);

        return $value;
    }
}
