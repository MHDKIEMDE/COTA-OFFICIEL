<?php

namespace App\Jobs;

use App\Models\FootballMatch;
use App\Services\FootballApiService;
use App\Services\OddsApiService;
use App\Services\PredictionAlgorithmService;
use App\Services\RapidApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Tournant toutes les heures, ce job vérifie si le quota API-Football
 * vient de se renouveler (minuit UTC). Si oui :
 *   1. Peupler la BD avec les résultats des 7 derniers jours (top ligues)
 *   2. Régénérer les prédictions du jour avec l'algo complet
 *
 * Il ne tourne qu'une fois par renouvellement grâce à un flag en cache.
 */
class RefreshDatabaseWhenQuotaRestoredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FootballApiService $footballApi, OddsApiService $oddsApi): void
    {
        $today   = Carbon::today('UTC')->toDateString();
        $flagKey = 'quota_restored_' . $today;

        // Éviter de tourner plusieurs fois le même jour
        if (Cache::has($flagKey)) {
            Log::debug('RefreshDatabaseWhenQuotaRestoredJob: déjà exécuté aujourd\'hui, skip');
            return;
        }

        // ── Vérifier si les prédictions du jour sont suffisantes ─────────────
        $predCount = DB::table('predictions')
            ->where('is_published', true)
            ->whereDate('match_date', $today)
            ->count();

        $isSufficient = $predCount >= 10;

        Log::info('RefreshDatabaseWhenQuotaRestoredJob: état prédictions', [
            'date'       => $today,
            'pred_count' => $predCount,
            'sufficient' => $isSufficient,
        ]);

        // Si on a déjà assez de prédictions avec de vraies cotes, rien à faire
        if ($isSufficient) {
            $withRealOdds = DB::table('predictions')
                ->where('is_published', true)
                ->whereDate('match_date', $today)
                ->whereRaw("JSON_EXTRACT(analysis_details, '$.odds_source') = '1xbet'")
                ->count();

            if ($withRealOdds >= 5) {
                Log::info('RefreshDatabaseWhenQuotaRestoredJob: prédictions suffisantes avec cotes réelles, skip', [
                    'avec_cotes_1xbet' => $withRealOdds,
                ]);
                Cache::put($flagKey, true, 86400);
                return;
            }
        }

        // ── Vérifier le quota API-Football ───────────────────────────────────
        try {
            $stats     = $footballApi->getUsageStats();
            $remaining = $stats['daily']['remaining'] ?? 0;
            $used      = $stats['daily']['used'] ?? 100;
        } catch (\Throwable $e) {
            Log::warning('RefreshDatabaseWhenQuotaRestoredJob: impossible de lire le quota', ['error' => $e->getMessage()]);
            return;
        }

        if ($remaining < 5) {
            Log::debug('RefreshDatabaseWhenQuotaRestoredJob: quota insuffisant pour le rattrapage', ['remaining' => $remaining]);
            return;
        }

        Log::info('RefreshDatabaseWhenQuotaRestoredJob: Rattrapage — rechargement cotes + régénération', [
            'quota_remaining' => $remaining,
            'pred_existantes' => $predCount,
        ]);

        Cache::put($flagKey, true, 86400);

        // ── Étape 1 : Historique récent pour avoir les scores frais ──────────
        $this->fetchRecentHistory($footballApi);

        // ── Étape 2 : Recharger les cotes 1xBet (cache expiré depuis 23h15) ─
        $oddsApi->clearCache();
        $oddsCount = $oddsApi->loadDailyOdds();
        Log::info('RefreshDatabaseWhenQuotaRestoredJob: cotes 1xBet rechargées', ['matchs' => $oddsCount]);

        // ── Étape 3 : Supprimer les prédictions fallback ou sans cotes réelles
        $deleted = \App\Models\Prediction::whereDate('match_date', $today)
            ->where(function ($q) {
                $q->where('match_id', 'like', 'rapi_%')
                  ->orWhereJsonContains('analysis_details->algorithm_version', 'fallback-v1');
            })
            ->delete();

        Log::info('RefreshDatabaseWhenQuotaRestoredJob: prédictions fallback supprimées', ['deleted' => $deleted]);

        // ── Étape 4 : Régénérer avec l'algo complet + nouvelles cotes ────────
        GenerateAllPredictionsJob::dispatch();
        Log::info('RefreshDatabaseWhenQuotaRestoredJob: GenerateAllPredictionsJob dispatché');
    }

    private function fetchRecentHistory(FootballApiService $footballApi): void
    {
        // Ligues prioritaires à peupler (top 5 européennes + Champions League)
        $topLeagues = [39, 140, 135, 78, 61, 2, 3];
        $season     = Carbon::now()->year;
        $fetched    = 0;

        foreach ($topLeagues as $leagueId) {
            try {
                // Dernières 5 journées de chaque ligue
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'x-apisports-key' => config('football-api.api_key'),
                ])->timeout(15)->get('https://v3.football.api-sports.io/fixtures', [
                    'league' => $leagueId,
                    'season' => $season,
                    'last'   => 5,
                    'status' => 'FT',
                ]);

                if (!$response->successful()) continue;

                foreach ($response->json('response', []) as $fixture) {
                    $this->storeFinishedMatch($fixture);
                    $fetched++;
                }

                Log::info("RefreshDB: ligue {$leagueId} → {$fetched} matchs peuplés");

            } catch (\Throwable $e) {
                Log::warning("RefreshDB: erreur ligue {$leagueId}", ['error' => $e->getMessage()]);
            }
        }

        Log::info('RefreshDatabaseWhenQuotaRestoredJob: Historique peuplé', ['total' => $fetched]);
    }

    private function storeFinishedMatch(array $fixture): void
    {
        $fixtureData = $fixture['fixture'] ?? [];
        $teams       = $fixture['teams']   ?? [];
        $goals       = $fixture['goals']   ?? [];
        $league      = $fixture['league']  ?? [];

        $matchId = (string) ($fixtureData['id'] ?? null);
        if (!$matchId) return;

        FootballMatch::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_team_id'   => $teams['home']['id']   ?? null,
                'away_team_id'   => $teams['away']['id']   ?? null,
                'competition_id' => $league['id']          ?? null,
                'home_team'      => $teams['home']['name'] ?? 'Unknown',
                'away_team'      => $teams['away']['name'] ?? 'Unknown',
                'competition'    => $league['name']        ?? 'Unknown',
                'country'        => $league['country']     ?? 'Unknown',
                'match_date'     => Carbon::parse($fixtureData['date'] ?? now()),
                'match_time'     => Carbon::parse($fixtureData['date'] ?? now())->format('H:i'),
                'home_score'     => $goals['home'] ?? null,
                'away_score'     => $goals['away'] ?? null,
                'status'         => 'finished',
                'venue_name'     => $fixtureData['venue']['name'] ?? null,
                'last_api_fetch' => now(),
            ]
        );
    }
}
