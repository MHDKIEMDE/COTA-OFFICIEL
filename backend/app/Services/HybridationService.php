<?php

namespace App\Services;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Log;

/**
 * Couche d'hybridation algo + source externe (§8 CDC V2).
 *
 * Formule :
 *   Score_Publié = Score_Algo × (1 - w_ext) + Score_Externe_normalisé × w_ext
 *
 * w_ext configurable dans AppConfig (clé : algo.w_ext).
 * Au lancement : 0.30–0.40 (algo non prouvé).
 * Dans le temps : réduit progressivement quand le backtest valide l'algo interne.
 *
 * Garde-fou désaccord (§8.4) :
 *   SI direction(Algo) ≠ direction(Externe) sur le résultat principal
 *   → ne pas publier ce pari OU rétrograder la confiance d'un palier.
 *
 * Traçabilité (§8.5) :
 *   Chaque prédiction stocke : score_algo, score_externe, score_publie, w_ext.
 */
class HybridationService
{
    // Valeur par défaut si AppConfig non renseignée
    private const DEFAULT_W_EXT = 0.35;

    // Seuils de direction (ratio normalisé 0–1 : >0.5 = domicile favori)
    private const DIRECTION_THRESHOLD = 0.10; // différence min pour considérer une direction

    /**
     * Applique la formule d'hybridation et retourne les données enrichies.
     *
     * @param array $predictionData  Sortie de PredictionAlgorithmService::generatePrediction()
     * @param array|null $external   Sortie normalisée de RapidApiService::normalizePrediction() ou null
     * @return array  predictionData enrichi + champs traçabilité + should_publish mis à jour
     */
    public function hybridize(array $predictionData, ?array $external): array
    {
        $scoreAlgo = (float) ($predictionData['confidence'] ?? 0);
        $wExt      = $this->getWExt();

        // Pas de source externe disponible → on publie avec l'algo seul
        if (!$external || $wExt <= 0) {
            return array_merge($predictionData, [
                'score_algo'    => $scoreAlgo,
                'score_externe' => null,
                'score_publie'  => $scoreAlgo,
                'w_ext'         => 0.0,
                'hybrid_source' => 'algo_only',
            ]);
        }

        // Normaliser la source externe en score 0–100
        $scoreExterne = $this->normalizeExternalScore($external, $predictionData);

        // Garde-fou désaccord de direction (§8.4)
        $disagreement = $this->detectDisagreement($predictionData, $external);

        if ($disagreement) {
            Log::info('HybridationService: désaccord direction, rétrogradation confiance', [
                'outcome'   => $predictionData['outcome'] ?? '',
                'algo_dir'  => $this->algoDirection($predictionData),
                'ext_pred'  => $external['prediction'] ?? null,
            ]);
            // Rétrograder d'un palier : réduire le score publié de 10 pts
            $scorePubile = max(0, $scoreAlgo - 10);
            return array_merge($predictionData, [
                'confidence'    => $scorePubile,
                'score_algo'    => $scoreAlgo,
                'score_externe' => $scoreExterne,
                'score_publie'  => $scorePubile,
                'w_ext'         => $wExt,
                'hybrid_source' => 'disagree_downgraded',
                // Recalculer should_publish et stars avec le nouveau score
                'should_publish' => $scorePubile >= 50,
                'stars'          => $this->starsFromScore($scorePubile),
                'is_premium'     => $this->starsFromScore($scorePubile) >= 3,
            ]);
        }

        // Mélange pondéré (§8.2)
        $scorePubile = ($scoreAlgo * (1 - $wExt)) + ($scoreExterne * $wExt);
        $scorePubile = round($scorePubile, 2);

        return array_merge($predictionData, [
            'confidence'    => $scorePubile,
            'score_algo'    => $scoreAlgo,
            'score_externe' => $scoreExterne,
            'score_publie'  => $scorePubile,
            'w_ext'         => $wExt,
            'hybrid_source' => 'blended',
            'should_publish' => $scorePubile >= 50,
            'stars'          => $this->starsFromScore($scorePubile),
            'is_premium'     => $this->starsFromScore($scorePubile) >= 3,
        ]);
    }

    /**
     * Lit w_ext depuis AppConfig, avec cache statique pour éviter N requêtes.
     */
    public function getWExt(): float
    {
        static $cached = null;
        if ($cached !== null) return $cached;

        $value = AppConfig::get('algo.w_ext', self::DEFAULT_W_EXT);
        $cached = (float) max(0.0, min(1.0, $value));
        return $cached;
    }

    /**
     * Normalise les probabilités de la source externe en score 0–100,
     * en tenant compte du type de pari de l'algo.
     *
     * Conversion : probabilité gagnante × 100 (centrée sur 50).
     */
    private function normalizeExternalScore(array $external, array $predictionData): float
    {
        $type    = $predictionData['type']    ?? '1X2';
        $outcome = $predictionData['outcome'] ?? '';

        // Marchés buts : utiliser over25 / btts comme signal
        if (in_array($type, ['Over/Under', 'BTTS'])) {
            if ($type === 'Over/Under' && str_contains($outcome, 'Over')) {
                return isset($external['over25']) ? ($external['over25'] ? 72.0 : 35.0) : 55.0;
            }
            if ($type === 'Over/Under' && str_contains($outcome, 'Under')) {
                return isset($external['over25']) ? ($external['over25'] ? 35.0 : 72.0) : 55.0;
            }
            if ($type === 'BTTS' && $outcome === 'Oui') {
                return isset($external['btts']) ? ($external['btts'] ? 70.0 : 38.0) : 55.0;
            }
            if ($type === 'BTTS' && $outcome === 'Non') {
                return isset($external['btts']) ? ($external['btts'] ? 38.0 : 70.0) : 55.0;
            }
            return 55.0;
        }

        // Marchés résultat (1X2, Double Chance, Handicap)
        $homeWin = (float) ($external['home_win_pct'] ?? 0);
        $draw    = (float) ($external['draw_pct']     ?? 0);
        $awayWin = (float) ($external['away_win_pct'] ?? 0);

        // Normaliser si les probabilités sont en % (0–100) ou en ratio (0–1)
        $sum = $homeWin + $draw + $awayWin;
        if ($sum > 1.5) { // format pourcentage
            $homeWin /= 100; $draw /= 100; $awayWin /= 100;
        }

        return match (true) {
            $outcome === '1'             => round($homeWin * 100, 1),
            $outcome === '2'             => round($awayWin * 100, 1),
            $outcome === 'X'             => round($draw * 100, 1),
            $outcome === '1X'            => round(($homeWin + $draw) * 100, 1),
            $outcome === 'X2'            => round(($draw + $awayWin) * 100, 1),
            default                      => 55.0,
        };
    }

    /**
     * Détecte un désaccord de direction entre algo et source externe (§8.4).
     * Désaccord = algo dit domicile gagne, externe dit extérieur gagne (ou inverse).
     */
    private function detectDisagreement(array $predictionData, array $external): bool
    {
        $extPrediction = strtoupper(trim($external['prediction'] ?? ''));
        if (!$extPrediction) return false;

        $algoDir = $this->algoDirection($predictionData);
        if (!$algoDir) return false;

        $extDir = match (true) {
            str_contains($extPrediction, '1') && !str_contains($extPrediction, '2') => 'home',
            str_contains($extPrediction, '2') && !str_contains($extPrediction, '1') => 'away',
            default => null,
        };

        if (!$extDir) return false;

        return $algoDir !== $extDir;
    }

    /**
     * Direction principale déduite du type/outcome de l'algo.
     */
    private function algoDirection(array $predictionData): ?string
    {
        $outcome = $predictionData['outcome'] ?? '';
        return match (true) {
            in_array($outcome, ['1', '1X']) => 'home',
            in_array($outcome, ['2', 'X2']) => 'away',
            default => null,
        };
    }

    private function starsFromScore(float $score): int
    {
        return match (true) {
            $score >= 85 => 4,
            $score >= 70 => 3,
            $score >= 60 => 2,
            $score >= 50 => 1,
            default      => 0,
        };
    }
}
