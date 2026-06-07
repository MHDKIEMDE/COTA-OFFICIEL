<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AllSportsApiService;
use App\Services\FlashscorePlayerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function __construct(
        private readonly AllSportsApiService     $allSports,
        private readonly FlashscorePlayerService $flashscore,
    ) {}

    /**
     * GET /api/players/search?q=...
     * Recherche joueur — AllSportsAPI2 (résultats rapides) + enrichissement Flashscore.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['success' => false, 'message' => 'Paramètre q requis (min 2 caractères)'], 422);
        }

        $players = $this->allSports->searchPlayer($query);

        return response()->json([
            'success' => true,
            'data'    => $players,
            'meta'    => ['count' => count($players), 'source' => 'allsportsapi2'],
        ]);
    }

    /**
     * GET /api/players/details?url=...
     * Détails complets depuis Flashscore (âge, valeur marchande, blessure, derniers matchs).
     * Le paramètre url = slug Flashscore ex: "mendes-nuno/ne2xCTJj/"
     */
    public function details(Request $request): JsonResponse
    {
        $url = trim((string) $request->query('url', ''));

        if (empty($url)) {
            return response()->json(['success' => false, 'message' => 'Paramètre url requis'], 422);
        }

        $player = $this->flashscore->getPlayerDetails($url);

        if ($player === null) {
            return response()->json(['success' => false, 'message' => 'Joueur introuvable'], 404);
        }

        return response()->json(['success' => true, 'data' => $player]);
    }

    /**
     * GET /api/standings/{tournamentId}/{seasonId}
     * Classement d'un tournoi via AllSportsAPI2.
     */
    public function standings(Request $request, string $tournamentId, string $seasonId): JsonResponse
    {
        $rows = $this->allSports->getStandings($tournamentId, $seasonId);

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'meta'    => [
                'count'        => count($rows),
                'tournament_id' => $tournamentId,
                'season_id'    => $seasonId,
                'source'       => 'allsportsapi2',
            ],
        ]);
    }
}
