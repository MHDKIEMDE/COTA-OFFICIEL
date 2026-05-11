<?php

namespace App\Jobs;

use App\Services\FootballApiService;
use App\Models\FootballMatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job pour récupérer les matchs depuis API-Football et les sauvegarder en base.
 * Exécuté toutes les heures pour synchroniser les matchs du jour et du lendemain.
 */
class FetchMatchesJob implements ShouldQueue
{
    use Queueable;

    protected int $daysAhead;

    public function __construct(int $daysAhead = 2)
    {
        $this->daysAhead = $daysAhead;
    }

    public function handle(FootballApiService $footballApi): void
    {
        Log::info('FetchMatchesJob: Démarrage synchronisation matchs API-Football');

        $matchesSaved   = 0;
        $matchesUpdated = 0;
        $errors         = 0;

        try {
            $response = $footballApi->getUpcomingMatches($this->daysAhead);

            if (!$response || empty($response['response'])) {
                Log::warning('FetchMatchesJob: Aucun match retourné par API-Football');
                return;
            }

            foreach ($response['response'] as $fixture) {
                try {
                    $this->saveMatch($fixture, $matchesSaved, $matchesUpdated);
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('FetchMatchesJob: Erreur sauvegarde match', [
                        'fixture_id' => $fixture['fixture']['id'] ?? 'unknown',
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            Log::info("FetchMatchesJob terminé: {$matchesSaved} créés, {$matchesUpdated} mis à jour, {$errors} erreurs");

        } catch (\Exception $e) {
            Log::error('FetchMatchesJob échoué: ' . $e->getMessage());
            throw $e;
        }
    }

    private function saveMatch(array $fixture, int &$saved, int &$updated): void
    {
        $fixtureId  = $fixture['fixture']['id'] ?? null;
        if (!$fixtureId) return;

        $matchId    = (string) $fixtureId;
        $date       = Carbon::parse($fixture['fixture']['date'] ?? now());
        $homeTeam   = $fixture['teams']['home'] ?? [];
        $awayTeam   = $fixture['teams']['away'] ?? [];
        $league     = $fixture['league'] ?? [];
        $goals      = $fixture['goals'] ?? [];
        $score      = $fixture['score'] ?? [];
        $fixtureInfo = $fixture['fixture'] ?? [];

        $status = $this->mapStatus($fixtureInfo['status']['short'] ?? 'NS');

        $data = [
            'match_id'            => $matchId,
            'home_team_id'        => $homeTeam['id'] ?? null,
            'away_team_id'        => $awayTeam['id'] ?? null,
            'competition_id'      => $league['id'] ?? null,
            'home_team'           => $homeTeam['name'] ?? 'Unknown',
            'away_team'           => $awayTeam['name'] ?? 'Unknown',
            'competition'         => $league['name'] ?? 'Unknown',
            'country'             => $league['country'] ?? 'Unknown',
            'competition_logo'    => $league['logo'] ?? null,
            'match_date'          => $date,
            'match_time'          => $date->format('H:i'),
            'timezone'            => 'UTC',
            'home_score'          => $goals['home'],
            'away_score'          => $goals['away'],
            'home_score_halftime' => $score['halftime']['home'] ?? null,
            'away_score_halftime' => $score['halftime']['away'] ?? null,
            'status'              => $status,
            'status_long'         => $fixtureInfo['status']['long'] ?? null,
            'elapsed_time'        => $fixtureInfo['status']['elapsed'] ?? null,
            'venue_name'          => $fixtureInfo['venue']['name'] ?? null,
            'venue_city'          => $fixtureInfo['venue']['city'] ?? null,
            'referee'             => $fixtureInfo['referee'] ?? null,
            'last_api_fetch'      => now(),
        ];

        $existing = FootballMatch::where('match_id', $matchId)->first();

        if ($existing) {
            $existing->update($data);
            $updated++;
        } else {
            FootballMatch::create($data);
            $saved++;
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
