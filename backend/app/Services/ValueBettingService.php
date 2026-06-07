<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Calcule la valeur espérée (EV) et la mise Kelly optimale pour chaque prédiction.
 *
 * Formules :
 *   Value Score  = (confidence/100) × odds - 1
 *   Kelly %      = (p × odds - 1) / (odds - 1)   où p = confidence/100
 *
 * Un pari a de la valeur (EV+) si value_score > 0.05 (marge de 5 %).
 */
class ValueBettingService
{
    private const EV_THRESHOLD     = 0.05;
    private const KELLY_MAX        = 0.10;  // jamais plus de 10 % du bankroll
    private const KELLY_FRACTION   = 0.25;  // Kelly fractionné : 1/4 du Kelly complet
    private const MIN_ODDS         = 1.05;  // Cote plancher pour le calcul

    // ── API publique ──────────────────────────────────────────────────────────

    /**
     * Calcule et retourne tous les champs Value Betting pour une prédiction.
     *
     * @param  float  $confidence  Score 0–100
     * @param  float  $odds        Cote estimée (ex : 1.80)
     * @return array{value_score: float, kelly_fraction: float, ev_positive: bool}
     */
    public function calculate(float $confidence, float $odds): array
    {
        $odds = max($odds, self::MIN_ODDS);
        $p    = $confidence / 100.0;

        $valueScore    = $this->calculateValueScore($p, $odds);
        $kellyFraction = $this->calculateKelly($p, $odds);
        $evPositive    = $valueScore > self::EV_THRESHOLD;

        return [
            'value_score'    => round($valueScore, 3),
            'kelly_fraction' => round($kellyFraction, 4),
            'ev_positive'    => $evPositive,
        ];
    }

    /**
     * Calcule la mise conseillée en FCFA à partir du bankroll courant.
     *
     * @param  float  $kellyFraction  Fraction Kelly (0.0 – 1.0)
     * @param  int    $bankroll       Bankroll disponible en FCFA
     * @return int                    Mise arrondie à 100 FCFA près
     */
    public function advisedStake(float $kellyFraction, int $bankroll): int
    {
        $raw    = $kellyFraction * $bankroll;
        $rounded = (int) round($raw / 100) * 100;
        return max($rounded, 0);
    }

    /**
     * Calcule le gain potentiel net pour une mise donnée.
     */
    public function potentialGain(float $odds, int $stake): int
    {
        return (int) round(($odds - 1) * $stake);
    }

    /**
     * Retourne un résumé lisible pour la notification / UI.
     *
     * Exemple : "💰 Valeur +26% — Mise conseillée 8 500 FCFA (gain potentiel 15 300 FCFA)"
     */
    public function summary(float $valueScore, float $kellyFraction, int $bankroll): string
    {
        $percent = (int) round($valueScore * 100);
        $stake   = $this->advisedStake($kellyFraction, $bankroll);
        $sign    = $percent >= 0 ? '+' : '';

        return "💰 Valeur {$sign}{$percent}% — Mise conseillée " . number_format($stake, 0, ',', ' ') . ' FCFA';
    }

    // ── Calculs internes ──────────────────────────────────────────────────────

    private function calculateValueScore(float $p, float $odds): float
    {
        return $p * $odds - 1.0;
    }

    private function calculateKelly(float $p, float $odds): float
    {
        if ($odds <= 1.0) {
            return 0.0;
        }

        $fullKelly = ($p * $odds - 1.0) / ($odds - 1.0);

        if ($fullKelly <= 0) {
            return 0.0;
        }

        // Kelly fractionné pour limiter le risque
        $fractional = $fullKelly * self::KELLY_FRACTION;

        return min($fractional, self::KELLY_MAX);
    }
}
