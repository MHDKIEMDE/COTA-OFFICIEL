<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FootballApiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function __construct(private readonly FootballApiService $footballApi)
    {
    }

    /**
     * GET /api/teams/{id}
     * Informations générales + stade
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $data = $this->footballApi->getTeamInfo((int) $id);
            $team = $data['response'][0] ?? null;

            if (!$team) {
                return response()->json(['success' => false, 'message' => 'Équipe introuvable'], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $this->normalizeTeam($team),
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::show({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/teams/{id}/stats?league=&season=
     * Stats saison : victoires, buts, forme, clean sheets
     */
    public function stats(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        $league = (int) $request->query('league', 39);
        $season = (int) $request->query('season', Carbon::now()->year);

        try {
            $data  = $this->footballApi->getTeamStatistics((int) $id, $season, $league);
            $stats = $data['response'] ?? null;

            if (!$stats) {
                return response()->json(['success' => false, 'message' => 'Stats non disponibles'], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $this->normalizeStats($stats),
                'meta'    => ['league' => $league, 'season' => $season],
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::stats({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/teams/{id}/matches?last=10
     * Derniers matchs de l'équipe
     */
    public function matches(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        $last = min((int) $request->query('last', 10), 20);

        try {
            $data     = $this->footballApi->getTeamRecentMatches((int) $id, $last);
            $fixtures = $data['response'] ?? [];

            $matches = array_map(fn($f) => $this->normalizeFixture($f), $fixtures);

            return response()->json([
                'success' => true,
                'data'    => $matches,
                'meta'    => ['count' => count($matches)],
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::matches({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/teams/{id}/squad
     * Effectif complet (noms, numéros, positions, âge)
     */
    public function squad(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $data    = $this->footballApi->getTeamSquad((int) $id);
            $squad   = $data['response'][0]['players'] ?? [];

            $players = array_map(fn($p) => [
                'id'       => $p['id'],
                'name'     => $p['name'],
                'age'      => $p['age'],
                'number'   => $p['number'],
                'position' => $this->translatePosition($p['position'] ?? ''),
                'photo'    => $p['photo'],
            ], $squad);

            usort($players, fn($a, $b) => $this->positionOrder($a['position']) <=> $this->positionOrder($b['position']));

            return response()->json([
                'success' => true,
                'data'    => $players,
                'meta'    => ['count' => count($players)],
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::squad({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/teams/{id}/transfers
     * Transferts récents (arrivées + départs)
     */
    public function transfers(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $data      = $this->footballApi->getTeamTransfers((int) $id);
            $transfers = $data['response'] ?? [];

            $result = [];
            foreach ($transfers as $item) {
                $player = $item['player'] ?? [];
                foreach ($item['transfers'] ?? [] as $transfer) {
                    $teamIn  = $transfer['teams']['in'] ?? [];
                    $teamOut = $transfer['teams']['out'] ?? [];
                    $teamId  = (int) $id;

                    $result[] = [
                        'player_id'   => $player['id'],
                        'player_name' => $player['name'],
                        'player_photo'=> $player['photo'] ?? null,
                        'type'        => ($teamIn['id'] ?? null) == $teamId ? 'in' : 'out',
                        'club_from'   => $teamOut['name'] ?? '?',
                        'club_to'     => $teamIn['name'] ?? '?',
                        'club_logo'   => ($teamIn['id'] ?? null) == $teamId
                            ? ($teamOut['logo'] ?? null)
                            : ($teamIn['logo'] ?? null),
                        'date'        => $transfer['date'] ?? null,
                        'fee'         => $transfer['type'] ?? 'N/D',
                    ];
                }
            }

            usort($result, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
            $result = array_slice($result, 0, 30);

            return response()->json([
                'success' => true,
                'data'    => $result,
                'meta'    => ['count' => count($result)],
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::transfers({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/teams/{id}/injuries?season=
     * Blessés et suspendus actuels
     */
    public function injuries(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        $season = (int) $request->query('season', Carbon::now()->year);

        try {
            $data    = $this->footballApi->getTeamInjuries((int) $id, $season);
            $entries = $data['response'] ?? [];

            $injuries = array_map(fn($e) => [
                'player_id'   => $e['player']['id'] ?? null,
                'player_name' => $e['player']['name'] ?? '?',
                'player_photo'=> $e['player']['photo'] ?? null,
                'reason'      => $e['player']['reason'] ?? 'Blessure',
                'type'        => $this->translateInjury($e['player']['type'] ?? ''),
            ], $entries);

            return response()->json([
                'success' => true,
                'data'    => $injuries,
                'meta'    => ['count' => count($injuries)],
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::injuries({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/teams/{id}/news
     * Articles récents traduits en français (Google News RSS)
     */
    public function news(Request $request, string $id): JsonResponse
    {
        try {
            // Récupérer le nom de l'équipe
            $teamData = $this->footballApi->getTeamInfo((int) $id);
            $teamName = $teamData['response'][0]['team']['name'] ?? null;

            if (!$teamName) {
                return response()->json(['success' => false, 'message' => 'Équipe introuvable'], 404);
            }

            $articles = $this->fetchGoogleNewsRss($teamName);

            return response()->json([
                'success' => true,
                'data'    => $articles,
                'meta'    => ['team' => $teamName, 'count' => count($articles)],
            ]);
        } catch (\Exception $e) {
            Log::error("TeamController::news({$id}) — " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Méthodes privées ──────────────────────────────────────────────────────

    private function normalizeTeam(array $item): array
    {
        $team  = $item['team'] ?? [];
        $venue = $item['venue'] ?? [];

        return [
            'id'          => $team['id'],
            'name'        => $team['name'],
            'short_name'  => $team['code'] ?? null,
            'logo'        => $team['logo'],
            'country'     => $team['country'],
            'founded'     => $team['founded'],
            'national'    => $team['national'] ?? false,
            'venue_name'  => $venue['name'] ?? null,
            'venue_city'  => $venue['city'] ?? null,
            'venue_image' => $venue['image'] ?? null,
            'venue_capacity' => $venue['capacity'] ?? null,
        ];
    }

    private function normalizeStats(array $stats): array
    {
        $fixtures = $stats['fixtures'] ?? [];
        $goals    = $stats['goals'] ?? [];
        $form     = $stats['form'] ?? '';

        return [
            'form'              => $form,
            'played_total'      => $fixtures['played']['total'] ?? 0,
            'played_home'       => $fixtures['played']['home'] ?? 0,
            'played_away'       => $fixtures['played']['away'] ?? 0,
            'wins_total'        => $fixtures['wins']['total'] ?? 0,
            'draws_total'       => $fixtures['draws']['total'] ?? 0,
            'losses_total'      => $fixtures['loses']['total'] ?? 0,
            'goals_for_total'   => $goals['for']['total']['total'] ?? 0,
            'goals_for_avg'     => (float) ($goals['for']['average']['total'] ?? 0),
            'goals_against_total' => $goals['against']['total']['total'] ?? 0,
            'goals_against_avg' => (float) ($goals['against']['average']['total'] ?? 0),
            'clean_sheets_total'=> $stats['clean_sheet']['total'] ?? 0,
            'failed_to_score'   => $stats['failed_to_score']['total'] ?? 0,
            'biggest_win_home'  => $stats['biggest']['wins']['home'] ?? null,
            'biggest_win_away'  => $stats['biggest']['wins']['away'] ?? null,
            'biggest_loss_home' => $stats['biggest']['loses']['home'] ?? null,
            'biggest_loss_away' => $stats['biggest']['loses']['away'] ?? null,
            'average_goals_per_game' => $stats['goals']['for']['average']['total'] ?? 0,
            'penalty_scored'    => $stats['penalty']['scored']['percentage'] ?? null,
            'penalty_missed'    => $stats['penalty']['missed']['percentage'] ?? null,
        ];
    }

    private function normalizeFixture(array $fixture): array
    {
        $info   = $fixture['fixture'] ?? [];
        $teams  = $fixture['teams'] ?? [];
        $goals  = $fixture['goals'] ?? [];
        $league = $fixture['league'] ?? [];
        $status = $info['status'] ?? [];

        return [
            'id'               => (string) ($info['id'] ?? ''),
            'date'             => $info['date'] ?? null,
            'home_team'        => $teams['home']['name'] ?? '',
            'home_team_id'     => $teams['home']['id'] ?? null,
            'home_team_logo'   => $teams['home']['logo'] ?? null,
            'away_team'        => $teams['away']['name'] ?? '',
            'away_team_id'     => $teams['away']['id'] ?? null,
            'away_team_logo'   => $teams['away']['logo'] ?? null,
            'home_score'       => $goals['home'],
            'away_score'       => $goals['away'],
            'competition'      => $league['name'] ?? '',
            'competition_logo' => $league['logo'] ?? null,
            'status'           => $status['short'] ?? 'NS',
            'venue'            => $info['venue']['name'] ?? null,
        ];
    }

    private function translatePosition(string $pos): string
    {
        return match ($pos) {
            'Goalkeeper'  => 'Gardien',
            'Defender'    => 'Défenseur',
            'Midfielder'  => 'Milieu',
            'Attacker'    => 'Attaquant',
            default       => $pos,
        };
    }

    private function translateInjury(string $type): string
    {
        return match (strtolower($type)) {
            'muscle injury' => 'Blessure musculaire',
            'knee injury'   => 'Blessure genou',
            'ankle injury'  => 'Blessure cheville',
            'suspension'    => 'Suspension',
            'illness'       => 'Maladie',
            default         => $type ?: 'Indisponible',
        };
    }

    private function positionOrder(string $pos): int
    {
        return match ($pos) {
            'Gardien'    => 0,
            'Défenseur'  => 1,
            'Milieu'     => 2,
            'Attaquant'  => 3,
            default      => 4,
        };
    }

    private function fetchGoogleNewsRss(string $teamName): array
    {
        try {
            $query   = urlencode("{$teamName} football");
            $url     = "https://news.google.com/rss/search?q={$query}&hl=fr&gl=FR&ceid=FR:fr";
            $content = @file_get_contents($url);

            if (!$content) return [];

            $xml      = simplexml_load_string($content);
            $articles = [];

            foreach ($xml->channel->item ?? [] as $item) {
                if (count($articles) >= 10) break;

                $articles[] = [
                    'title'       => (string) $item->title,
                    'url'         => (string) $item->link,
                    'source'      => (string) $item->source,
                    'published_at'=> (string) $item->pubDate,
                    'description' => strip_tags((string) ($item->description ?? '')),
                ];
            }

            return $articles;
        } catch (\Exception $e) {
            Log::warning("TeamController::fetchGoogleNewsRss — " . $e->getMessage());
            return [];
        }
    }
}
