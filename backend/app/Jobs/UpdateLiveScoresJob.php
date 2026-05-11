<?php

namespace App\Jobs;

use App\Services\FootballApiService;
use App\Models\FootballMatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job pour mettre à jour les scores en direct depuis API-Football.
 * Exécuté toutes les 2 minutes pour les matchs en cours.
 */
class UpdateLiveScoresJob implements ShouldQueue
{
    use Queueable;

    public function handle(FootballApiService $footballApi): void
    {
        Log::info('UpdateLiveScoresJob: Mise à jour scores en direct');

        $matchesUpdated = 0;

        try {
            $response = $footballApi->getLiveMatches();

            if (!$response || empty($response['response'])) {
                Log::info('UpdateLiveScoresJob: Aucun match en direct');
                return;
            }

            Log::info('UpdateLiveScoresJob: ' . count($response['response']) . ' matchs en direct');

            foreach ($response['response'] as $fixture) {
                try {
                    $fixtureId = $fixture['fixture']['id'] ?? null;
                    if (!$fixtureId) continue;

                    $matchId = (string) $fixtureId;
                    $status  = $this->mapStatus($fixture['fixture']['status']['short'] ?? 'NS');

                    $footballMatch = FootballMatch::where('match_id', $matchId)->first();

                    if (!$footballMatch) {
                        Log::warning("UpdateLiveScoresJob: match {$matchId} non trouvé en base");
                        continue;
                    }

                    $footballMatch->update([
                        'home_score'          => $fixture['goals']['home'],
                        'away_score'          => $fixture['goals']['away'],
                        'home_score_halftime' => $fixture['score']['halftime']['home'] ?? null,
                        'away_score_halftime' => $fixture['score']['halftime']['away'] ?? null,
                        'status'              => $status,
                        'status_long'         => $fixture['fixture']['status']['long'] ?? null,
                        'elapsed_time'        => $fixture['fixture']['status']['elapsed'] ?? null,
                        'last_api_fetch'      => now(),
                    ]);

                    $matchesUpdated++;

                } catch (\Exception $e) {
                    Log::error('UpdateLiveScoresJob: Erreur mise à jour', [
                        'fixture_id' => $fixture['fixture']['id'] ?? 'unknown',
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            Log::info("UpdateLiveScoresJob terminé: {$matchesUpdated} matchs mis à jour");

        } catch (\Exception $e) {
            Log::error('UpdateLiveScoresJob échoué: ' . $e->getMessage());
            throw $e;
        }
    }

    private function mapStatus(string $short): string
    {
        return match ($short) {
            'FT', 'AET', 'PEN' => 'finished',
            '1H', '2H', 'ET'   => 'live',
            'HT'               => 'halftime',
            'PST'              => 'postponed',
            'CANC'             => 'cancelled',
            'ABD'              => 'abandoned',
            default            => 'scheduled',
        };
    }
}
