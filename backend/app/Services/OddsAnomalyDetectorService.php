<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OddsAnomaly;
use App\Models\Prediction;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Détecte les anomalies de cotes en comparant :
 *   - Cotes live 1xBet (via RapidApiService)
 *   - Cotes de référence agrégées (RapidAPI football-prediction-api)
 *
 * Une anomalie = écart > GAP_THRESHOLD entre le bookmaker et le marché de référence.
 * Durée de vie d'une anomalie : ANOMALY_TTL_MINUTES (les bookmakers corrigent vite).
 */
class OddsAnomalyDetectorService
{
    private const GAP_THRESHOLD       = 35.0;  // écart % minimum pour déclarer anomalie
    private const ANOMALY_TTL_MINUTES = 20;    // durée de vie de l'anomalie
    private const MIN_ODD             = 1.05;  // cotes trop basses ignorées
    private const MAX_ODD             = 15.0;  // cotes trop hautes = cote de niche, ignorées

    public function __construct(private readonly RapidApiService $rapidApi) {}

    // ── Point d'entrée principal ──────────────────────────────────────────────

    /**
     * Scanne les prédictions du jour et détecte les anomalies de cotes.
     * Retourne le nombre d'anomalies nouvellement détectées.
     */
    public function scan(): int
    {
        $detected = 0;

        // 1. Charger les cotes live 1xBet
        $liveOdds = $this->rapidApi->get1xBetLiveOdds();
        if (empty($liveOdds)) {
            Log::info('OddsAnomalyDetector: aucune cote 1xBet live disponible');
            return 0;
        }

        // 2. Charger les prédictions du jour avec cotes de référence RapidAPI
        $predictions = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->where('status', 'pending')
            ->get();

        if ($predictions->isEmpty()) {
            return 0;
        }

        // 3. Construire index 1xBet normalisé : "home vs away" → odds
        $liveIndex = $this->buildLiveIndex($liveOdds);

        // 4. Comparer pour chaque prédiction
        foreach ($predictions as $prediction) {
            $key      = $this->normalizeKey($prediction->home_team, $prediction->away_team);
            $live1x   = $liveIndex[$key] ?? null;

            if (!$live1x) continue;

            // Cote de référence = cote stockée en base (issue de RapidAPI / algo)
            $refOdds = $this->buildReferenceOdds($prediction);

            foreach (['1' => 'home_win', 'X' => 'draw', '2' => 'away_win'] as $outcome => $field) {
                $liveOdd = (float) ($live1x[$field] ?? 0);
                $refOdd  = (float) ($refOdds[$outcome] ?? 0);

                if ($liveOdd < self::MIN_ODD || $liveOdd > self::MAX_ODD) continue;
                if ($refOdd  < self::MIN_ODD || $refOdd  > self::MAX_ODD) continue;

                $gap = $this->gapPercent($liveOdd, $refOdd);

                if (abs($gap) >= self::GAP_THRESHOLD) {
                    $saved = $this->saveAnomaly($prediction, $outcome, '1xbet', $liveOdd, $refOdd, $gap);
                    if ($saved) $detected++;
                }
            }
        }

        Log::info("OddsAnomalyDetector: scan terminé", [
            'predictions_scanned' => $predictions->count(),
            'anomalies_detected'  => $detected,
        ]);

        return $detected;
    }

    // ── Construction des index ────────────────────────────────────────────────

    private function buildLiveIndex(array $liveOdds): array
    {
        $index = [];
        foreach ($liveOdds as $match) {
            $home = $match['home_team'] ?? $match['home'] ?? null;
            $away = $match['away_team'] ?? $match['away'] ?? null;
            if (!$home || !$away) continue;

            $key = $this->normalizeKey($home, $away);
            $index[$key] = [
                'home_win' => (float) ($match['home']  ?? $match['home_win'] ?? 0),
                'draw'     => (float) ($match['draw']  ?? 0),
                'away_win' => (float) ($match['away']  ?? $match['away_win'] ?? 0),
            ];
        }
        return $index;
    }

    private function buildReferenceOdds(Prediction $prediction): array
    {
        // Utiliser les cotes stockées comme référence selon le type de pari
        $stored = (float) $prediction->odds;
        $pred   = $prediction->prediction;

        return [
            '1' => $pred === '1'  ? $stored : $this->estimateFromStored($stored, '1',  $pred),
            'X' => $pred === 'X'  ? $stored : $this->estimateFromStored($stored, 'X',  $pred),
            '2' => $pred === '2'  ? $stored : $this->estimateFromStored($stored, '2',  $pred),
        ];
    }

    /**
     * Estime les cotes non stockées à partir de la cote principale.
     * Approximation : si on a la cote du favori (ex 1.40), les autres sont déduites.
     */
    private function estimateFromStored(float $stored, string $target, string $stored_outcome): float
    {
        if ($stored < self::MIN_ODD) return 0.0;

        // Probabilité implicite du résultat stocké
        $p = 1 / $stored;

        // Distribuer le reste entre X et l'autre outcome
        $pRest = 1 - $p;

        return match (true) {
            $stored_outcome === '1' && $target === 'X'  => round(1 / ($pRest * 0.55), 2),
            $stored_outcome === '1' && $target === '2'  => round(1 / ($pRest * 0.45), 2),
            $stored_outcome === '2' && $target === 'X'  => round(1 / ($pRest * 0.55), 2),
            $stored_outcome === '2' && $target === '1'  => round(1 / ($pRest * 0.45), 2),
            $stored_outcome === 'X' && $target === '1'  => round(1 / ($pRest * 0.50), 2),
            $stored_outcome === 'X' && $target === '2'  => round(1 / ($pRest * 0.50), 2),
            default => 0.0,
        };
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

    private function normalizeKey(string $home, string $away): string
    {
        return strtolower(trim($home) . ' vs ' . trim($away));
    }

    // ── Nettoyage ─────────────────────────────────────────────────────────────

    public function purgeExpired(): int
    {
        return OddsAnomaly::where('expires_at', '<', now())->delete();
    }
}
