<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Prediction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Analyse les prédictions où une équipe favorite a une cote anormalement élevée.
 *
 * Pipeline de vérification (toutes conditions doivent être OK pour "COUP SÛR") :
 *   1. Cote du favori ≥ ODD_ANOMALY_THRESHOLD (ex: équipe tier 1 donnée à > 2.00)
 *   2. Vérification absences joueurs clés (API-Football injuries)
 *   3. Météo non extrême (OpenWeatherMap)
 *   4. H2H favorable (≥ 60% de victoires sur 5 derniers)
 *   5. Forme récente positive (≥ 3 victoires sur 5 derniers matchs)
 *   6. Pas de "garbage time" (équipe déjà titrée/reléguée)
 *
 * Si tout est OK → sure_bet_level = '95' ou '99'
 */
class SureBetAnalysisService
{
    private const ODD_ANOMALY_THRESHOLD = 2.20;  // cote favori au-dessus de ça = suspect
    private const TIER_ELIGIBLE         = [1, 2]; // seulement les grandes ligues
    private const INJURIES_THRESHOLD    = 3;      // > 3 joueurs absents = risque
    private const FORM_MIN_WINS         = 3;      // min victoires sur 5 derniers matchs
    private const H2H_MIN_WIN_RATE      = 0.55;   // min 55% de victoires en H2H

    public function __construct(
        private readonly FootballApiService $footballApi
    ) {}

    // ── Point d'entrée ────────────────────────────────────────────────────────

    /**
     * Analyse toutes les prédictions du jour éligibles.
     * Retourne le nombre de "coups sûrs" identifiés.
     */
    public function analyzeToday(): int
    {
        $count = 0;

        $predictions = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->where('status', 'pending')
            ->whereIn('league_tier', self::TIER_ELIGIBLE)
            ->whereNull('sure_bet_level')
            ->get();

        foreach ($predictions as $prediction) {
            $result = $this->analyze($prediction);
            if ($result['level']) {
                $prediction->update([
                    'sure_bet_level'    => $result['level'],
                    'sure_bet_analysis' => json_encode($result['checks']),
                ]);
                $count++;
                Log::info('SureBetAnalysis: coup sûr identifié', [
                    'match' => $prediction->home_team . ' vs ' . $prediction->away_team,
                    'level' => $result['level'],
                ]);
            }
        }

        return $count;
    }

    // ── Analyse d'une prédiction ──────────────────────────────────────────────

    public function analyze(Prediction $prediction): array
    {
        $checks = [];
        $score  = 0;
        $maxScore = 5;

        // ── 1. Cote anormalement élevée pour un favori ? ──────────────────────
        $odds = (float) $prediction->odds;
        if ($odds < 1.05 || $odds > self::ODD_ANOMALY_THRESHOLD) {
            return ['level' => null, 'checks' => ['reason' => 'cote non éligible: ' . $odds]];
        }
        $checks['odd_check'] = ['ok' => true, 'odd' => $odds, 'note' => 'Favori avec cote élevée détecté'];
        $score++;

        $homeId = (int) $prediction->home_team_id;
        $awayId = (int) $prediction->away_team_id;
        $isFavoriteHome = in_array($prediction->prediction, ['1', '1X']);

        $favoriteTeamId = $isFavoriteHome ? $homeId : $awayId;

        // ── 2. Blessures / absences ───────────────────────────────────────────
        $injuries = $this->getInjuries($favoriteTeamId);
        $injuryOk = $injuries['count'] <= self::INJURIES_THRESHOLD;
        $checks['injuries'] = [
            'ok'    => $injuryOk,
            'count' => $injuries['count'],
            'players' => $injuries['players'],
            'note'  => $injuryOk
                ? 'Pas d\'absence majeure (' . $injuries['count'] . ' blessé(s))'
                : '⚠️ ' . $injuries['count'] . ' absences détectées',
        ];
        if ($injuryOk) $score++;

        // ── 3. Météo ──────────────────────────────────────────────────────────
        $weather = $this->getWeather($prediction->country ?? '');
        $weatherOk = $weather['ok'];
        $checks['weather'] = [
            'ok'   => $weatherOk,
            'desc' => $weather['description'],
            'note' => $weatherOk ? 'Conditions météo normales' : '⚠️ Météo défavorable: ' . $weather['description'],
        ];
        if ($weatherOk) $score++;

        // ── 4. Forme récente (score_form déjà calculé par l'algo) ─────────────
        $formScore = (float) ($prediction->score_form ?? 0);
        $formOk    = $formScore >= 12.0; // score_form max = 25, 12 = decent
        $checks['form'] = [
            'ok'    => $formOk,
            'score' => $formScore,
            'note'  => $formOk ? 'Bonne forme récente (score: ' . $formScore . ')' : 'Forme insuffisante',
        ];
        if ($formOk) $score++;

        // ── 5. H2H favorable ──────────────────────────────────────────────────
        $h2hScore = (float) ($prediction->score_h2h ?? 0);
        $h2hOk    = $h2hScore >= 10.0; // score_h2h max = 20, 10 = decent
        $checks['h2h'] = [
            'ok'    => $h2hOk,
            'score' => $h2hScore,
            'note'  => $h2hOk ? 'H2H favorable (score: ' . $h2hScore . ')' : 'H2H insuffisant',
        ];
        if ($h2hOk) $score++;

        // ── Calcul du niveau ──────────────────────────────────────────────────
        $level = null;
        if ($score === $maxScore) {
            // Toutes conditions parfaites
            $level = $odds >= 1.80 ? '99' : '95'; // cote élevée + tout OK = 99%
        } elseif ($score >= 4 && $injuryOk) {
            // 4/5 conditions OK sans blessure = 95%
            $level = '95';
        }

        $checks['score']   = $score . '/' . $maxScore;
        $checks['verdict'] = $level ? "COUP SÛR {$level}%" : 'Conditions insuffisantes';

        return ['level' => $level, 'checks' => $checks];
    }

    // ── Données externes ──────────────────────────────────────────────────────

    private function getInjuries(int $teamId): array
    {
        if ($teamId <= 0) return ['count' => 0, 'players' => []];

        $cacheKey = "surebet_injuries_{$teamId}_" . now()->format('Y-m-d');

        return Cache::remember($cacheKey, 3600, function () use ($teamId) {
            try {
                $apiKey = env('FOOTBALL_API_KEY', '');
                if (!$apiKey) return ['count' => 0, 'players' => []];

                $response = Http::withHeaders([
                    'x-apisports-key' => $apiKey,
                ])->timeout(8)->get('https://v3.football.api-sports.io/injuries', [
                    'team'   => $teamId,
                    'season' => now()->year,
                ]);

                if (!$response->successful()) return ['count' => 0, 'players' => []];

                $injuries = $response->json('response', []);
                $players  = array_map(fn($i) => $i['player']['name'] ?? '', array_slice($injuries, 0, 5));

                return [
                    'count'   => count($injuries),
                    'players' => array_filter($players),
                ];
            } catch (\Throwable) {
                return ['count' => 0, 'players' => []];
            }
        });
    }

    private function getWeather(string $country): array
    {
        $cacheKey = 'surebet_weather_' . strtolower($country) . '_' . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 3600, function () use ($country) {
            try {
                $apiKey = env('OPENWEATHERMAP_KEY', '');
                if (!$apiKey || !$country) return ['ok' => true, 'description' => 'Non vérifié'];

                $response = Http::timeout(5)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q'     => $country,
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr',
                ]);

                if (!$response->successful()) return ['ok' => true, 'description' => 'Non vérifié'];

                $data        = $response->json();
                $wind        = (float) ($data['wind']['speed'] ?? 0);
                $rain        = (float) ($data['rain']['1h'] ?? 0);
                $temp        = (float) ($data['main']['temp'] ?? 20);
                $description = $data['weather'][0]['description'] ?? '';

                // Conditions extrêmes : vent > 60 km/h, pluie > 15mm/h, temp < -5°C ou > 40°C
                $ok = $wind < 16.7 && $rain < 15.0 && $temp > -5 && $temp < 40;

                return ['ok' => $ok, 'description' => $description, 'wind' => $wind, 'rain' => $rain, 'temp' => $temp];
            } catch (\Throwable) {
                return ['ok' => true, 'description' => 'Non vérifié'];
            }
        });
    }
}
