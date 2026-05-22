<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RapidApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private readonly RapidApiService $rapidApi)
    {
    }

    /**
     * GET /api/media/highlights?home=PSG&away=Lyon
     * Highlights vidéo d'un match terminé.
     */
    public function highlights(Request $request): JsonResponse
    {
        $request->validate([
            'home' => 'required|string|max:100',
            'away' => 'required|string|max:100',
        ]);

        $videos = $this->rapidApi->getMatchHighlights(
            $request->query('home'),
            $request->query('away')
        );

        return response()->json([
            'success' => true,
            'data'    => $videos,
            'count'   => count($videos),
        ]);
    }

    /**
     * GET /api/media/highlights/latest
     * Derniers highlights du jour (non filtrés par match).
     */
    public function latestHighlights(Request $request): JsonResponse
    {
        $limit  = min((int) $request->query('limit', 10), 20);
        $videos = $this->rapidApi->getLatestHighlights($limit);

        return response()->json([
            'success' => true,
            'data'    => $videos,
            'count'   => count($videos),
        ]);
    }

    /**
     * GET /api/media/streams
     * Liste des streams live football disponibles.
     */
    public function streams(Request $request): JsonResponse
    {
        $streams = $this->rapidApi->getLiveStreams();

        return response()->json([
            'success' => true,
            'data'    => $streams,
            'count'   => count($streams),
        ]);
    }

    /**
     * GET /api/media/streams/match?home=PSG&away=Lyon
     * Stream pour un match spécifique.
     */
    public function streamForMatch(Request $request): JsonResponse
    {
        $request->validate([
            'home' => 'required|string|max:100',
            'away' => 'required|string|max:100',
        ]);

        $stream = $this->rapidApi->getStreamForMatch(
            $request->query('home'),
            $request->query('away')
        );

        if ($stream === null) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun stream disponible pour ce match.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $stream,
        ]);
    }
}
