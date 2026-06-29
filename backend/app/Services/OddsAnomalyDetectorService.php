<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OddsAnomaly;
use App\Models\Prediction;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Détecte les anomalies de cotes (value bets) en comparant :
 *   - Cote bookmaker réelle 1xBet (via OddsApiService / The Odds API)
 *   - Cote « juste » issue de notre algorithme (prediction->odds)
 *
 * Une anomalie = écart > GAP_THRESHOLD entre la cote bookmaker et notre cote.
 * Durée de vie d'une anomalie : ANOMALY_TTL_MINUTES (les bookmakers corrigent vite).
 */
class OddsAnomalyDetectorService
{
    private const GAP_THRESHOLD       = 35.0;  // écart % minimum pour déclarer anomalie
    private const ANOMALY_TTL_MINUTES = 20;    // durée de vie de l'anomalie
    private const MIN_ODD             = 1.05;  // cotes trop basses ignorées
    private const MAX_ODD             = 15.0;  // cotes trop hautes = cote de niche, ignorées

    public function __construct(private readonly OddsApiService $oddsApi) {}

    // ── Point d'entrée principal ──────────────────────────────────────────────

    /**
     * Scanne les prédictions du jour et détecte les anomalies de cotes.
     * Retourne le nombre d'anomalies nouvellement détectées.
     */
    public function scan(): int
    {
        $detected = 0;

        // 1. Charger les cotes réelles 1xBet du jour (The Odds API, cache 4h)
        $indexed = $this->oddsApi->loadDailyOdds();
        if ($indexed === 0) {
            Log::info('OddsAnomalyDetector: aucune cote bookmaker disponible');
            return 0;
        }

        // 2. Charger les prédictions publiées du jour encore en attente
        $predictions = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->where('status', 'pending')
            ->get();

        if ($predictions->isEmpty()) {
            return 0;
        }

        // 3. Comparer la cote bookmaker réelle à notre cote « juste » (algo)
        foreach ($predictions as $prediction) {
            $bookOdds = $this->oddsApi->findStrict($prediction->home_team, $prediction->away_team);
            if (!$bookOdds) continue;

            $outcome  = $prediction->prediction;          // '1' | 'X' | '2'
            $bookOdd  = $this->bookmakerOddFor($bookOdds, $outcome);
            $ourOdd   = (float) $prediction->odds;        // cote juste de notre algo

            if ($bookOdd === null) continue;
            if ($bookOdd < self::MIN_ODD || $bookOdd > self::MAX_ODD) continue;
            if ($ourOdd  < self::MIN_ODD || $ourOdd  > self::MAX_ODD) continue;

            $gap = $this->gapPercent($bookOdd, $ourOdd);

            // Value bet = le bookmaker propose nettement PLUS que notre cote juste
            if ($gap >= self::GAP_THRESHOLD) {
                $saved = $this->saveAnomaly($prediction, $outcome, '1xbet', $bookOdd, $ourOdd, $gap);
                if ($saved) $detected++;
            }
        }

        Log::info("OddsAnomalyDetector: scan terminé", [
            'predictions_scanned' => $predictions->count(),
            'anomalies_detected'  => $detected,
        ]);

        return $detected;
    }

    /**
     * Extrait la cote bookmaker correspondant au résultat prédit.
     * Les marchés over/under utilisent l'outcome textuel de la prédiction.
     */
    private function bookmakerOddFor(array $bookOdds, string $outcome): ?float
    {
        $val = match ($outcome) {
            '1' => $bookOdds['home'] ?? null,
            'X' => $bookOdds['draw'] ?? null,
            '2' => $bookOdds['away'] ?? null,
            default => null,
        };

        return $val !== null ? (float) $val : null;
    }

    // ── Sauvegarde ───────────────────────────────────────────────────────────

    private function saveAnomaly(
        Prediction $prediction,
        string $outcome,
        string $bookmaker,
        float $liveOdd,
        float $refOdd,
        float $gap
    ): bool {
        // Éviter les doublons (même match + outcome dans les 30 dernières minutes)
        $exists = OddsAnomaly::where('match_id', $prediction->match_id)
            ->where('outcome', $outcome)
            ->where('bookmaker', $bookmaker)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();

        if ($exists) return false;

        OddsAnomaly::create([
            'match_id'    => $prediction->match_id,
            'home_team'   => $prediction->home_team,
            'away_team'   => $prediction->away_team,
            'competition' => $prediction->competition,
            'country'     => $prediction->country,
            'match_date'  => $prediction->match_date,
            'bet_type'    => 'h2h',
            'outcome'     => $outcome,
            'bookmaker'   => $bookmaker,
            'odd_value'   => round($liveOdd, 2),
            'market_odd'  => round($refOdd, 2),
            'gap_pct'     => round(abs($gap), 1),
            'is_overpriced' => $liveOdd > $refOdd,
            'notified'    => false,
            'expires_at'  => now()->addMinutes(self::ANOMALY_TTL_MINUTES),
        ]);

        Log::info('OddsAnomalyDetector: anomalie détectée', [
            'match'     => $prediction->home_team . ' vs ' . $prediction->away_team,
            'outcome'   => $outcome,
            'bookmaker' => $bookmaker,
            'odd_value' => $liveOdd,
            'ref_odd'   => $refOdd,
            'gap_pct'   => round(abs($gap), 1),
        ]);

        return true;
    }

    // ── Utilitaires ───────────────────────────────────────────────────────────

    private function gapPercent(float $odd, float $ref): float
    {
        if ($ref <= 0) return 0.0;
        return (($odd - $ref) / $ref) * 100;
    }

    // ── Nettoyage ─────────────────────────────────────────────────────────────

    public function purgeExpired(): int
    {
        return OddsAnomaly::where('expires_at', '<', now())->delete();
    }
}
