<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
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
     * GET /api/standings/{competition}/top-scorers
     */
    public function topScorers(Request $request, string $competition): JsonResponse
    {
        if (!is_numeric($competition)) {
            return response()->json(['success' => false, 'message' => 'ID numérique requis'], 422);
        }

        try {
            $season   = (int) $request->query('season', Carbon::now()->year);
            $response = $this->footballApi->getTopScorers((int) $competition, $season);
            $players  = $response['response'] ?? [];

            $data = array_map(fn(array $p) => [
                'rank'   => $p['statistics'][0]['goals']['total'] ?? 0,
                'name'   => $p['player']['name'] ?? '?',
                'photo'  => $p['player']['photo'] ?? null,
                'team'   => $p['statistics'][0]['team']['name'] ?? '',
                'goals'  => $p['statistics'][0]['goals']['total'] ?? 0,
                'assists'=> $p['statistics'][0]['goals']['assists'] ?? 0,
                'games'  => $p['statistics'][0]['games']['appearences'] ?? 0,
            ], array_slice($players, 0, 20));

            return response()->json(['success' => true, 'data' => $data, 'meta' => ['league_id' => $competition, 'season' => $season]]);
        } catch (\Exception $e) {
            Log::error("MatchController::topScorers({$competition}) — " . $e->getMessage());
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

    /**
     * GET /api/matches/featured
     *
     * Retourne :
     *   • featured  — le "match choc" de la semaine (meilleur score contextuel sur 7 jours)
     *   • today     — liste des autres matchs du jour pour le tickertape (slug, cote, type)
     *
     * Le featured est caché à la semaine (même match 7 jours).
     * Le today est caché 30 min (rafraîchi régulièrement).
     */
    public function featured(): JsonResponse
    {
        try {
            // ── 1. Match choc de la semaine (cache hebdomadaire) ──────────────
            $weekKey      = now()->format('Y-W');
            $featuredKey  = 'matches:featured:week:' . $weekKey;
            $ttlWeek      = max((int) now()->endOfWeek(Carbon::SUNDAY)->diffInSeconds(now()), 3600);

            $featuredData = Cache::remember($featuredKey, $ttlWeek, function () {
                $response = $this->footballApi->getUpcomingMatches(7);
                $fixtures = $response['response'] ?? [];
                if (empty($fixtures)) return null;

                $leagueConfig = config('football-api.popular_leagues', []);
                $scored       = [];

                foreach ($fixtures as $fixture) {
                    $leagueId = $fixture['league']['id'] ?? null;
                    $tier     = $leagueConfig[$leagueId]['tier'] ?? 99;
                    $scored[] = ['fixture' => $fixture, 'tier' => $tier, 'score' => $this->computeMatchScore($fixture, $tier)];
                }

                $relevant = array_filter($scored, fn($s) => $s['tier'] <= 4);
                if (empty($relevant)) $relevant = $scored;

                usort($relevant, fn($a, $b) => $b['score'] <=> $a['score']);
                $best = array_values($relevant)[0] ?? null;
                if (!$best) return null;

                return $this->buildFeaturedPayload($best['fixture'], $best['tier']);
            });

            if (!$featuredData) {
                return response()->json(['success' => false, 'message' => 'Aucun match disponible'], 404);
            }

            // ── 2. Matchs du jour pour tickertape (cache 30 min) ──────────────
            $todayKey  = 'matches:featured:today:' . now()->format('Y-m-d');
            $todayData = Cache::remember($todayKey, 1800, function () {
                // Source : prédictions déjà générées en base — 0 appel API supplémentaire
                $predictions = Prediction::query()
                    ->whereDate('match_date', today())
                    ->whereNotNull('odds')
                    ->where('is_published', true)
                    ->orderByDesc('total_score')
                    ->limit(8)
                    ->get(['home_team', 'away_team', 'odds', 'bet_type', 'competition', 'match_date']);

                $result = $predictions->map(function (Prediction $p): array {
                    $homeSlug = $this->teamMono($p->home_team);
                    $awaySlug = $this->teamMono($p->away_team);

                    // Raccourcir le label bet_type pour le ticker
                    $betLabel = match ($p->bet_type) {
                        '1X2'          => '1',
                        'Over/Under'   => 'O2.5',
                        'BTTS'         => 'BTTS',
                        'Double Chance'=> 'DC',
                        default        => $p->bet_type,
                    };

                    return [
                        'slug'       => $homeSlug . '–' . $awaySlug,
                        'home_slug'  => $homeSlug,
                        'away_slug'  => $awaySlug,
                        'odds'       => (float) $p->odds,
                        'bet_type'   => $betLabel,
                        'league'     => $p->competition ?? '',
                        'start_time' => $p->match_date,
                    ];
                })->values()->all();

                return $result;
            });

            return response()->json([
                'success'  => true,
                'data'     => $featuredData,
                'today'    => $todayData,
            ]);

        } catch (\Exception $e) {
            Log::error('MatchController::featured — ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }

    /** Construit le payload enrichi pour le match featured. */
    private function buildFeaturedPayload(array $fixture, int $tier): array
    {
        $norm     = $this->normalize($fixture);
        $homeName = $fixture['teams']['home']['name'] ?? '';
        $awayName = $fixture['teams']['away']['name'] ?? '';
        $homeSlug = $this->teamMono($homeName);
        $awaySlug = $this->teamMono($awayName);

        // Journée ex: "Regular Season - 34" → "J34"
        $round        = $fixture['league']['round'] ?? '';
        $journeeLabel = $this->parseJourneeLabel($round);

        // Heure locale ex: "CE SOIR 21H" / "DEMAIN 20H" / "MER. 19H"
        $timeLabel = $this->parseKickoffLabel($fixture['fixture']['date'] ?? null);

        // Slug compétition court ex: "L1", "PL", "CL"
        $leagueSlug = $this->leagueSlug($fixture['league']['id'] ?? 0, $fixture['league']['name'] ?? '');

        $norm['home_color']   = $this->teamColor($homeName);
        $norm['away_color']   = $this->teamColor($awayName);
        $norm['home_mono']    = $homeSlug;
        $norm['away_mono']    = $awaySlug;
        $norm['league_tier']  = $tier;
        $norm['league_slug']  = $leagueSlug;
        $norm['journee']      = $journeeLabel;
        $norm['time_label']   = $timeLabel;
        $norm['pill_label']   = trim($leagueSlug . ' · ' . $journeeLabel . ' · ' . $timeLabel, ' ·');

        return $norm;
    }

    /** Génère un label de journée lisible depuis le round API. */
    private function parseJourneeLabel(string $round): string
    {
        // "Regular Season - 34" → "J34"
        if (preg_match('/(\d+)$/', $round, $m)) {
            return 'J' . $m[1];
        }
        // "Final" / "Semi-finals" → label direct
        $lower = strtolower($round);
        if (str_contains($lower, 'final') && !str_contains($lower, 'semi')) return 'FINALE';
        if (str_contains($lower, 'semi'))   return 'DEMI-FINALE';
        if (str_contains($lower, 'quarter')) return 'QUART';
        if (str_contains($lower, 'group'))   return 'POULES';
        return $round ?: '';
    }

    /** Génère un label d'heure contextuel : "CE SOIR 21H", "DEMAIN 20H", "MER. 19H". */
    private function parseKickoffLabel(?string $isoDate): string
    {
        if (!$isoDate) return '';

        try {
            $tz      = config('football-api.timezone', 'UTC');
            $kickoff = Carbon::parse($isoDate)->setTimezone($tz);
            $hour    = $kickoff->format('G') . 'H';
            $today   = Carbon::today($tz);

            return match (true) {
                $kickoff->isSameDay($today)              => 'CE SOIR ' . $hour,
                $kickoff->isSameDay($today->copy()->addDay()) => 'DEMAIN ' . $hour,
                default => strtoupper($kickoff->locale('fr')->isoFormat('ddd')) . '. ' . $hour,
            };
        } catch (\Throwable) {
            return '';
        }
    }

    /** Retourne un slug court pour la compétition : PL, L1, CL, WC… */
    private function leagueSlug(int $leagueId, string $leagueName): string
    {
        $map = [
            2   => 'CL',  3  => 'EL',  39 => 'PL',  40 => 'CH',
            61  => 'L1',  62 => 'L2',  78 => 'BL',  79 => 'BL2',
            135 => 'SA', 136 => 'SB', 140 => 'LL', 141 => 'SD',
            848 => 'UCL', 1  => 'WC',
        ];
        if (isset($map[$leagueId])) return $map[$leagueId];

        // Fallback : 2 premières majuscules des mots
        $words = preg_split('/\s+/', $leagueName);
        return strtoupper(implode('', array_map(fn($w) => substr($w, 0, 1), array_slice($words, 0, 3))));
    }

    /**
     * Estime une cote et un type de pari simple pour le tickertape.
     * Basé sur les stats disponibles dans le fixture API-Football.
     */
    private function estimateOdds(array $fixture): array
    {
        // Si les cotes bookmaker sont disponibles dans la réponse API
        $bookmakers = $fixture['bookmakers'] ?? [];
        foreach ($bookmakers as $bk) {
            foreach ($bk['bets'] ?? [] as $bet) {
                if (($bet['name'] ?? '') === 'Match Winner') {
                    $values = collect($bet['values'] ?? []);
                    $home   = (float) ($values->firstWhere('value', 'Home')['odd'] ?? 0);
                    $draw   = (float) ($values->firstWhere('value', 'Draw')['odd'] ?? 0);
                    $away   = (float) ($values->firstWhere('value', 'Away')['odd'] ?? 0);
                    if ($home > 0) {
                        $best = min(array_filter([$home, $draw, $away]));
                        return ['value' => round($best, 2), 'type' => '1X2'];
                    }
                }
            }
        }

        // Fallback déterministe basé sur l'ID du fixture (pas de hasard)
        $seed   = ($fixture['fixture']['id'] ?? 1) % 6;
        $combos = [
            ['value' => 1.65, 'type' => '1'],
            ['value' => 1.78, 'type' => '+2.5'],
            ['value' => 1.55, 'type' => 'BTTS'],
            ['value' => 2.10, 'type' => 'DC'],
            ['value' => 1.90, 'type' => 'O2.5'],
            ['value' => 1.72, 'type' => '2'],
        ];
        return $combos[$seed];
    }

    /**
     * Calcule un score d'attractivité pour un match — sans favoriser d'équipe spécifique.
     *
     * Critères contextuels universels (valables club, sélection, Coupe du Monde…) :
     *   • Tier de ligue         : tier 1 = +160 pts, tier 2 = +140, tier 3 = +120, tier 4 = +100
     *   • Tour de compétition   : finale +50, demi +40, quart +30, 8e/16e +20, groupe +10
     *   • Heure prime time      : coup d'envoi entre 17h et 22h (locale) = +15
     *   • Proximité temporelle  : dans les 48h = +10, 3–4 jours = +5
     *   • Logo disponible       : les deux équipes ont un logo renseigné = +5
     */
    private function computeMatchScore(array $fixture, int $tier): int
    {
        $score = 0;

        // — Tier de ligue (principal différenciateur) —
        $score += match (true) {
            $tier === 1 => 160,
            $tier === 2 => 140,
            $tier === 3 => 120,
            $tier === 4 => 100,
            default     => 0,
        };

        // — Tour de compétition (détecté depuis fixture.league.round) —
        $round = strtolower($fixture['league']['round'] ?? '');
        $score += match (true) {
            str_contains($round, 'final')         && !str_contains($round, 'semi') => 50,
            str_contains($round, 'semi')                                            => 40,
            str_contains($round, 'quarter')       || str_contains($round, 'quart') => 30,
            str_contains($round, '16')            || str_contains($round, '8')     => 20,
            str_contains($round, 'group')         || str_contains($round, 'groupe') => 10,
            default                                                                 => 0,
        };

        // — Heure prime time (17h–22h heure du fixture) —
        $dateStr = $fixture['fixture']['date'] ?? null;
        if ($dateStr) {
            try {
                $kickoff = Carbon::parse($dateStr)->setTimezone(config('football-api.timezone', 'UTC'));
                $hour    = (int) $kickoff->format('G');
                if ($hour >= 17 && $hour <= 22) $score += 15;

                // Proximité : plus c'est proche, plus c'est mis en avant
                $daysAway = (int) now()->diffInDays($kickoff, false);
                $score += match (true) {
                    $daysAway >= 0 && $daysAway <= 2 => 10,
                    $daysAway >= 3 && $daysAway <= 4 => 5,
                    default                           => 0,
                };
            } catch (\Throwable) {}
        }

        // — Les deux équipes ont un logo renseigné —
        $homeLogo = $fixture['teams']['home']['logo'] ?? null;
        $awayLogo = $fixture['teams']['away']['logo'] ?? null;
        if ($homeLogo && $awayLogo) $score += 5;

        return $score;
    }

    private function teamColor(string $name): string
    {
        $colors = [
            'Paris Saint Germain' => '#004899', 'PSG'              => '#004899',
            'Olympique Marseille' => '#6CBAE7', 'Marseille'        => '#6CBAE7',
            'Real Madrid'         => '#FFFFFF', 'Manchester City'  => '#6CABDD',
            'Barcelona'           => '#A50044', 'Bayern Munich'    => '#DC052D',
            'Liverpool'           => '#C8102E', 'Arsenal'          => '#EF0107',
            'Chelsea'             => '#034694', 'Manchester United'=> '#DA291C',
            'Juventus'            => '#000000', 'AC Milan'         => '#FB090B',
            'Inter'               => '#010E80', 'Atletico Madrid'  => '#CB3524',
            'Borussia Dortmund'   => '#FDE100', 'Ajax'             => '#D2122E',
            'Porto'               => '#003087', 'Benfica'          => '#D90A1E',
        ];

        foreach ($colors as $key => $color) {
            if (stripos($name, $key) !== false) return $color;
        }

        return '#1D2026';
    }

    private function teamMono(string $name): string
    {
        $map = [
            'Paris Saint Germain' => 'PSG', 'PSG'               => 'PSG',
            'Olympique Marseille' => 'OM',  'Marseille'         => 'OM',
            'Real Madrid'         => 'RMA', 'Manchester City'   => 'MCI',
            'Barcelona'           => 'FCB', 'Bayern Munich'     => 'BAY',
            'Liverpool'           => 'LIV', 'Arsenal'           => 'ARS',
            'Chelsea'             => 'CHE', 'Manchester United' => 'MAN',
            'Juventus'            => 'JUV', 'AC Milan'          => 'MIL',
            'Inter'               => 'INT', 'Atletico Madrid'   => 'ATL',
            'Borussia Dortmund'   => 'BVB', 'Ajax'              => 'AJX',
        ];

        foreach ($map as $key => $mono) {
            if (stripos($name, $key) !== false) return $mono;
        }

        // Fallback : 3 premières lettres en majuscule
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 2));
        }
        return strtoupper(substr($name, 0, 3));
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
