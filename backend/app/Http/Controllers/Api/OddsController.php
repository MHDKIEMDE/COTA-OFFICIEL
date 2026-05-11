<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OddsApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour gérer les cotes des bookmakers
 * 
 * IMPORTANT: Ce controller ne stocke PAS les données en base de données
 * Il fait uniquement du proxy vers l'API externe avec cache
 */
class OddsController extends Controller
{
    protected $oddsService;

    public function __construct(OddsApiService $oddsService)
    {
        $this->oddsService = $oddsService;
    }

    /**
     * GET /api/odds/match/{matchId}
     * 
     * Récupère les cotes en temps réel pour un match
     * PAS de stockage en base de données
     * 
     * @param Request $request
     * @param string $matchId ID du match (fixture_id ou sportradar_id)
     * @return JsonResponse
     */
    public function getMatchOdds(Request $request, string $matchId): JsonResponse
    {
        $sportKey = $request->query('sport', 'soccer');
        $bookmakersParam = $request->query('bookmakers');
        
        $bookmakers = $bookmakersParam 
            ? explode(',', $bookmakersParam)
            : ['bet365', '1xbet', 'betway', 'betwinner'];

        Log::info("📊 Demande cotes pour match: {$matchId} (sport: {$sportKey})");

        $odds = $this->oddsService->getMatchOdds($sportKey, $matchId, $bookmakers);

        if (!$odds) {
            return response()->json([
                'success' => false,
                'message' => 'Cotes non disponibles pour ce match',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $odds,
            'cached' => false, // TODO: Indiquer si c'est du cache
        ]);
    }

    /**
     * GET /api/odds/batch
     * 
     * Récupère les cotes pour plusieurs matchs en une requête
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getBatchOdds(Request $request): JsonResponse
    {
        $matchIdsParam = $request->query('match_ids', '');
        $sportKey = $request->query('sport', 'soccer');
        $bookmakersParam = $request->query('bookmakers');
        
        if (empty($matchIdsParam)) {
            return response()->json([
                'success' => false,
                'message' => 'Le paramètre match_ids est requis (format: id1,id2,id3)',
            ], 400);
        }

        $matchIds = array_filter(array_map('trim', explode(',', $matchIdsParam)));
        
        if (empty($matchIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun match_id valide fourni',
            ], 400);
        }

        // Limiter à 20 matchs par requête
        if (count($matchIds) > 20) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 20 matchs par requête',
            ], 400);
        }

        $bookmakers = $bookmakersParam 
            ? explode(',', $bookmakersParam)
            : ['bet365', '1xbet', 'betway', 'betwinner'];

        Log::info("📊 Demande batch cotes pour " . count($matchIds) . " matchs");

        $results = $this->oddsService->getBatchOdds($sportKey, $matchIds, $bookmakers);

        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => count($results),
        ]);
    }

    /**
     * GET /api/odds/bookmakers
     * 
     * Liste des bookmakers disponibles
     * 
     * @return JsonResponse
     */
    public function getBookmakers(): JsonResponse
    {
        $bookmakers = $this->oddsService->getAvailableBookmakers();

        return response()->json([
            'success' => true,
            'data' => $bookmakers,
        ]);
    }
}
