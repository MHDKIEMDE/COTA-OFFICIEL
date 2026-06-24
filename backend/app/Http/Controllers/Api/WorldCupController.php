<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Services\ZafronixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints Coupe du Monde 2026 — données structurées Zafronix.
 */
class WorldCupController extends Controller
{
    public function __construct(
        private readonly ZafronixService $zafronix,
    ) {
    }

    /**
     * GET /api/world-cup/tournament
     */
    public function tournament(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', 2026);
        $data = $this->zafronix->getTournament($year);

        return response()->json([
            'success' => $data !== null,
            'data'    => $data,
            'meta'    => ['year' => $year, 'source' => 'zafronix'],
        ], $data !== null ? 200 : 404);
    }

    /**
     * GET /api/world-cup/teams
     */
    public function teams(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', 2026);
        $data = $this->zafronix->getTeams($year);

        return response()->json([
            'success' => !empty($data),
            'data'    => $data,
            'meta'    => ['year' => $year, 'count' => count($data), 'source' => 'zafronix'],
        ]);
    }

    /**
     * GET /api/world-cup/matches
     */
    public function matches(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', 2026);
        $data = $this->zafronix->getMatches($year);

        return response()->json([
            'success' => !empty($data),
            'data'    => $data,
            'meta'    => ['year' => $year, 'count' => count($data), 'source' => 'zafronix'],
        ]);
    }

    /**
     * GET /api/world-cup/matches/{matchId}
     * Détail complet : heure, buteurs, compositions, cartons + nb de pronostics COTA.
     */
    public function match(Request $request, string $matchId): JsonResponse
    {
        $data = $this->zafronix->getMatch($matchId);

        if ($data === null) {
            return response()->json(['success' => false, 'message' => 'Match introuvable'], 404);
        }

        $data['predictions_count'] = $this->countPredictions($data['home_team'], $data['away_team']);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => ['match_id' => $matchId, 'source' => 'zafronix'],
        ]);
    }

    /**
     * Nombre de pronostics COTA enregistrés pour cette affiche (par noms d'équipes).
     */
    private function countPredictions(string $home, string $away): int
    {
        return Prediction::where('home_team', 'like', "%{$home}%")
            ->where('away_team', 'like', "%{$away}%")
            ->count();
    }
}
