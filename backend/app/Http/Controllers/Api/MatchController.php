<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FootballApiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MatchController extends Controller
{
    public function __construct(private readonly FootballApiService $footballApi)
    {
    }

    /**
     * GET /api/matches/live
     */
    public function live(Request $request): JsonResponse
    {
        try {
            $response = $this->footballApi->getLiveMatches();
            $matches  = [];

            foreach ($response['response'] ?? [] as $fixture) {
                $matches[] = $this->normalize($fixture);
            }

            return response()->json(['success' => true, 'data' => $matches, 'meta' => ['count' => count($matches)]]);
        } catch (\Exception $e) {
            Log::error('MatchController::live — ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => [], 'meta' => ['count' => 0]]);
        }
    }

    /**
     * GET /api/matches/today
     * Optional query: ?date=YYYY-MM-DD
     */
    public function today(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        return $this->byDate($request, $date);
    }

    /**
     * GET /api/matches/date/{date}
     */
    public function byDate(Request $request, string $date): JsonResponse
    {
        try {
            $parsed = Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return response()->json(['success' => false, 'message' => 'Date invalide. Format: YYYY-MM-DD'], 422);
        }

        $competition = $request->query('competition');
        $cacheKey    = "matches:today:{$parsed}" . ($competition ? ":{$competition}" : '');

        try {
            $matches = Cache::remember($cacheKey, 300, function () use ($parsed, $competition) {
                $target   = Carbon::parse($parsed);
                $today    = Carbon::today();
                $days     = max(0, (int) $today->diffInDays($target, false));
                $response = $this->footballApi->getUpcomingMatches($days + 1);
                $result   = [];

                foreach ($response['response'] ?? [] as $fixture) {
                    $fixtureDate = Carbon::parse($fixture['fixture']['date'])->format('Y-m-d');
                    if ($fixtureDate !== $parsed) continue;

                    if ($competition) {
                        $leagueId   = (string) ($fixture['league']['id'] ?? '');
                        $leagueName = $fixture['league']['name'] ?? '';
                        if ($competition !== $leagueId && $competition !== $leagueName) continue;
                    }

                    $result[] = $this->normalize($fixture);
                }

                return $result;
            });

            return response()->json([
                'success' => true,
                'data'    => $matches,
                'meta'    => ['source' => 'api-football', 'date' => $parsed, 'count' => count($matches)],
            ]);
        } catch (\Exception $e) {
            Log::error('MatchController::byDate — ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => [], 'meta' => ['date' => $parsed, 'count' => 0]]);
        }
    }

    /**
     * GET /api/matches/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $response = $this->footballApi->getMatchDetails((int) $id);
            $fixture  = $response['response'][0] ?? null;

            if (!$fixture) {
                return response()->json(['success' => false, 'message' => 'Match introuvable'], 404);
            }

            return response()->json(['success' => true, 'data' => ['match' => $this->normalize($fixture)]]);
        } catch (\Exception $e) {
            Log::error("MatchController::show({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/matches/{id}/events
     */
    public function events(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $response = $this->footballApi->getMatchEvents((int) $id);
            $events   = array_map(fn($e) => [
                'elapsed' => $e['time']['elapsed'] ?? null,
                'type'    => $e['type'] ?? null,
                'detail'  => $e['detail'] ?? null,
                'team'    => $e['team'] ?? null,
                'player'  => $e['player'] ?? null,
                'assist'  => $e['assist'] ?? null,
            ], $response['response'] ?? []);

            return response()->json(['success' => true, 'data' => ['events' => $events], 'meta' => ['count' => count($events)]]);
        } catch (\Exception $e) {
            Log::error("MatchController::events({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/matches/{id}/lineups
     */
    public function lineups(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $response     = $this->footballApi->getMatchLineups((int) $id);
            $matchDetails = $this->footballApi->getMatchDetails((int) $id);
            $homeTeamId   = $matchDetails['response'][0]['teams']['home']['id'] ?? null;
            $awayTeamId   = $matchDetails['response'][0]['teams']['away']['id'] ?? null;

            $home = null;
            $away = null;

            foreach ($response['response'] ?? [] as $lineup) {
                $teamId = $lineup['team']['id'] ?? null;
                if ($teamId == $homeTeamId) $home = $lineup;
                elseif ($teamId == $awayTeamId) $away = $lineup;
            }

            return response()->json(['success' => true, 'data' => ['home' => $home, 'away' => $away]]);
        } catch (\Exception $e) {
            Log::error("MatchController::lineups({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/standings/{competition}
     */
    public function standings(Request $request, string $competition): JsonResponse
    {
        if (!is_numeric($competition)) {
            return response()->json(['success' => false, 'message' => 'ID de compétition numérique requis'], 422);
        }

        try {
            $season   = (int) $request->query('season', Carbon::now()->year);
            $response = $this->footballApi->getStandings((int) $competition, $season);
            $data     = $response['response'][0] ?? null;

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Classement non disponible'], 404);
            }

            return response()->json(['success' => true, 'data' => $data, 'meta' => ['league_id' => $competition, 'season' => $season]]);
        } catch (\Exception $e) {
            Log::error("MatchController::standings({$competition}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/matches/{id}/stats
     * Statistiques du match : possession, tirs, corners, fautes…
     */
    public function stats(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $response = $this->footballApi->getMatchStats((int) $id);
            $raw      = $response['response'] ?? [];

            // Transformer en [team_id => [stat_name => value]]
            $stats = [];
            foreach ($raw as $teamStats) {
                $teamId   = $teamStats['team']['id'] ?? null;
                $teamName = $teamStats['team']['name'] ?? '';
                $teamLogo = $teamStats['team']['logo'] ?? null;
                $items    = [];

                foreach ($teamStats['statistics'] ?? [] as $stat) {
                    $key        = $this->normalizeStatKey($stat['type'] ?? '');
                    $items[$key] = $stat['value'];
                }

                $stats[] = [
                    'team_id'   => $teamId,
                    'team_name' => $teamName,
                    'team_logo' => $teamLogo,
                    'stats'     => $items,
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => $stats,
                'meta'    => ['fixture_id' => (int) $id],
            ]);
        } catch (\Exception $e) {
            Log::error("MatchController::stats({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/matches/{id}/h2h
     */
    public function h2h(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $matchDetails = $this->footballApi->getMatchDetails((int) $id);
            $fixture      = $matchDetails['response'][0] ?? null;

            if (!$fixture) {
                return response()->json(['success' => false, 'message' => 'Match introuvable'], 404);
            }

            $homeTeamId = $fixture['teams']['home']['id'];
            $awayTeamId = $fixture['teams']['away']['id'];
            $response   = $this->footballApi->getHeadToHead($homeTeamId, $awayTeamId, 10);
            $matches    = array_map(fn($f) => $this->normalize($f), $response['response'] ?? []);

            return response()->json(['success' => true, 'data' => ['matches' => $matches], 'meta' => ['count' => count($matches)]]);
        } catch (\Exception $e) {
            Log::error("MatchController::h2h({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Normalisation fixture API-Football → format uniforme ──────────────────

    private function normalizeStatKey(string $type): string
    {
        return match ($type) {
            'Ball Possession'        => 'possession',
            'Total Shots'            => 'shots_total',
            'Shots on Goal'          => 'shots_on_target',
            'Shots off Goal'         => 'shots_off_target',
            'Blocked Shots'          => 'shots_blocked',
            'Corner Kicks'           => 'corners',
            'Fouls'                  => 'fouls',
            'Yellow Cards'           => 'yellow_cards',
            'Red Cards'              => 'red_cards',
            'Goalkeeper Saves'       => 'saves',
            'Total passes'           => 'passes_total',
            'Passes accurate'        => 'passes_accurate',
            'Passes %'               => 'passes_accuracy',
            'expected_goals'         => 'xg',
            default                  => strtolower(str_replace(' ', '_', $type)),
        };
    }

    private function normalize(array $fixture): array
    {
        $info   = $fixture['fixture'] ?? [];
        $teams  = $fixture['teams'] ?? [];
        $goals  = $fixture['goals'] ?? [];
        $league = $fixture['league'] ?? [];
        $status = $info['status'] ?? [];

        return [
            'id'               => (string) ($info['id'] ?? ''),
            'start_time'       => $info['date'] ?? null,
            'home_team'        => $teams['home']['name'] ?? 'N/A',
            'away_team'        => $teams['away']['name'] ?? 'N/A',
            'home_team_logo'   => $teams['home']['logo'] ?? null,
            'away_team_logo'   => $teams['away']['logo'] ?? null,
            'home_score'       => $goals['home'],
            'away_score'       => $goals['away'],
            'competition'      => $league['name'] ?? 'N/A',
            'competition_id'   => (string) ($league['id'] ?? ''),
            'competition_logo' => $league['logo'] ?? null,
            'country'          => $league['country'] ?? null,
            'status'           => $this->mapStatus($status['short'] ?? 'NS'),
            'match_status'     => $status['long'] ?? null,
            'elapsed_time'     => $status['elapsed'] ?? null,
            'venue'            => $info['venue']['name'] ?? null,
        ];
    }

    private function mapStatus(string $short): string
    {
        return match ($short) {
            '1H', '2H', 'ET', 'P', 'LIVE' => 'live',
            'HT'                           => 'halftime',
            'FT', 'AET', 'PEN'             => 'finished',
            'PST', 'CANC', 'ABD', 'AWD'   => 'cancelled',
            default                        => 'scheduled',
        };
    }
}
