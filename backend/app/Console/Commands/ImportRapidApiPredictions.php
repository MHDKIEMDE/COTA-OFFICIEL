<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Services\RapidApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Importe les pronostics du jour directement depuis RapidAPI
 * (football-prediction-api) comme SOURCE PRINCIPALE.
 *
 * L'API renvoie pour chaque match un pronostic déjà calculé (1/X/2/1X/X2/12)
 * + toutes les cotes. On les mappe sur les tables matches + predictions.
 *
 * 1 seul appel API/jour (cache 6h dans RapidApiService).
 */
class ImportRapidApiPredictions extends Command
{
    protected $signature = 'predictions:import-rapidapi
        {--date= : Date ISO (Y-m-d), défaut aujourd\'hui}
        {--include-expired : Importer aussi les matchs déjà commencés/terminés}';

    protected $description = 'Importe les pronostics du jour depuis RapidAPI (source principale : prono + cotes prêts)';

    public function handle(RapidApiService $rapid): int
    {
        $date = $this->option('date') ?: now()->format('Y-m-d');
        $all  = $rapid->loadDailyThirdPartyPredictions($date);

        if (empty($all)) {
            $this->warn("Aucun pronostic RapidAPI pour $date.");
            return self::SUCCESS;
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($all as $item) {
            if (empty($item['prediction']) || empty($item['home_team']) || empty($item['away_team'])) {
                $skipped++;
                continue;
            }
            if (($item['is_expired'] ?? false) && !$this->option('include-expired')) {
                $skipped++;
                continue;
            }

            $this->importOne($item);
            $imported++;
        }

        $this->invalidateCaches($date);

        $this->info("Importés : $imported pronostic(s) ($skipped ignorés) pour $date.");
        $this->line('Caches pronostics + coupon invalidés → coupon resynchronisé.');
        return self::SUCCESS;
    }

    /**
     * Resynchronise pronostics ↔ coupon : invalide le cache des prédictions
     * (version) et celui du coupon du jour (clé fixe, à oublier explicitement).
     */
    private function invalidateCaches(string $date): void
    {
        Cache::increment('predictions_cache_version');
        foreach (['free', 'premium'] as $tier) {
            Cache::forget("coupon_v3_{$date}_{$tier}");
        }
    }

    private function importOne(array $item): void
    {
        $matchId  = 'rapid_' . ($item['ext_id'] ?? md5($item['home_team'] . $item['away_team'] . ($item['start_date'] ?? '')));
        $startDate = $item['start_date'] ? Carbon::parse($item['start_date']) : now();

        FootballMatch::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_team'   => $item['home_team'],
                'away_team'   => $item['away_team'],
                'home_team_id' => 0,
                'away_team_id' => 0,
                'competition_id' => 0,
                'competition' => $item['competition_name'] ?? 'Unknown',
                'country'     => $item['competition_cluster'] ?? 'World',
                'match_date'  => $startDate,
                'match_time'  => $startDate->format('H:i'),
                'status'      => ($item['is_expired'] ?? false) ? 'finished' : 'scheduled',
            ]
        );

        [$betType, $outcome, $odds] = $this->mapPrediction($item);
        $stars = $this->starsFromOdds($odds);

        // Résultat final si le match est joué (historique gagné/perdu).
        [$homeScore, $awayScore, $status] = $this->resolveResult($item);

        Prediction::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_score'       => $homeScore,
                'away_score'       => $awayScore,
                'home_team'        => $item['home_team'],
                'away_team'        => $item['away_team'],
                'home_team_id'     => 0,
                'away_team_id'     => 0,
                'competition_id'   => 0,
                'competition'      => $item['competition_name'] ?? 'Unknown',
                'country'          => $item['competition_cluster'] ?? 'World',
                'match_date'       => $startDate,
                'match_time'       => $startDate->format('H:i'),
                'bet_type'         => $betType,
                'prediction'       => $outcome,
                'odds'             => $odds ?? 0,
                'confidence_stars' => $stars,
                'total_score'      => $this->scoreFromStars($stars),
                'score_algo'       => $this->scoreFromStars($stars),
                'engine_used'      => 'rapidapi',
                'analysis_source'  => 'rapidapi',
                'analysis_details' => json_encode([
                    'odds_source'  => 'market', // cotes réelles du marché (pas estimées)
                    'all_odds'     => $item['odds'] ?? [],
                    'raw_pred'     => $item['prediction'] ?? null,
                ]),
                'analysis_text'    => $this->buildAnalysis($item, $betType, $outcome, $odds),
                'status'           => $status,
                'is_published'     => true,
                'is_premium'       => $stars >= 3,
            ]
        );
    }

    /**
     * Résout le résultat final d'un match depuis le payload RapidAPI.
     *
     * football-prediction-api renvoie, une fois le match joué :
     *   - result = "1 - 1" (score final)
     *   - status = won|lost (verdict du pronostic, déjà calculé côté API)
     * Tant que le match n'est pas joué, on reste pending sans score.
     *
     * @return array{0:?int,1:?int,2:string} [home_score, away_score, status COTA]
     */
    private function resolveResult(array $item): array
    {
        $rawStatus = strtolower((string) ($item['result_status'] ?? ''));
        $result    = (string) ($item['result'] ?? '');

        // Match non joué : aucun score, statut en attente.
        if (!($item['is_expired'] ?? false) || $result === '') {
            return [null, null, 'pending'];
        }

        // Parse "1 - 1" → [1, 1]. Tolère espaces variables.
        $home = null;
        $away = null;
        if (preg_match('/(\d+)\s*-\s*(\d+)/', $result, $m)) {
            $home = (int) $m[1];
            $away = (int) $m[2];
        }

        // Le verdict won/lost est fourni directement par l'API.
        $status = match ($rawStatus) {
            'won'  => 'won',
            'lost' => 'lost',
            default => $home !== null ? 'finished' : 'pending',
        };

        return [$home, $away, $status];
    }

    /**
     * Mappe le pronostic RapidAPI (1/X/2/1X/X2/12) vers bet_type/outcome/odds.
     *
     * @return array{0:string,1:string,2:float|null}
     */
    private function mapPrediction(array $item): array
    {
        $pred = (string) $item['prediction'];
        $odds = $item['odds'] ?? [];
        $home = $item['home_team'];
        $away = $item['away_team'];

        $oddVal = isset($odds[$pred]) ? (float) $odds[$pred] : null;

        return match ($pred) {
            '1'  => ['1X2', $home, $oddVal],
            '2'  => ['1X2', $away, $oddVal],
            'X'  => ['1X2', 'Match nul', $oddVal],
            '1X' => ['Double Chance', "$home ou nul", $oddVal],
            'X2' => ['Double Chance', "Nul ou $away", $oddVal],
            '12' => ['Double Chance', "$home ou $away", $oddVal],
            default => ['1X2', $home, $oddVal],
        };
    }

    /**
     * Étoiles dérivées de la cote : cote basse = forte probabilité = plus fiable.
     */
    private function starsFromOdds(?float $odds): int
    {
        if ($odds === null || $odds <= 0) return 1;
        if ($odds <= 1.40) return 4;
        if ($odds <= 1.70) return 3;
        if ($odds <= 2.20) return 2;
        return 1;
    }

    private function scoreFromStars(int $stars): float
    {
        return match ($stars) {
            4 => 88.0,
            3 => 75.0,
            2 => 64.0,
            default => 55.0,
        };
    }

    private function buildAnalysis(array $item, string $betType, string $outcome, ?float $odds): string
    {
        $cote = $odds ? sprintf(' (cote %.2f)', $odds) : '';
        return sprintf(
            'Pronostic %s : %s%s. Analyse fournie par football-prediction-api d\'après les probabilités du marché. Pariez de façon responsable.',
            $betType,
            $outcome,
            $cote
        );
    }
}
