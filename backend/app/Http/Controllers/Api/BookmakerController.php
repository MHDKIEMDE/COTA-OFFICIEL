<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmaker;
use App\Models\BookmakerClick;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour la gestion des bookmakers (configurables depuis Admin)
 */
class BookmakerController extends Controller
{
    /**
     * Récupérer la liste des bookmakers actifs
     * 
     * GET /api/bookmakers
     * Accessible sans authentification (liste publique)
     */
    public function index(): JsonResponse
    {
        $bookmakers = Bookmaker::active()
            ->ordered()
            ->get()
            ->map(function ($bookmaker) {
                return [
                    'id' => $bookmaker->id,
                    'name' => $bookmaker->name,
                    'slug' => $bookmaker->slug,
                    'primary_color' => $bookmaker->primary_color,
                    'secondary_color' => $bookmaker->secondary_color,
                    'affiliate_link' => $bookmaker->affiliate_link,
                    'download_link' => $bookmaker->download_link,
                    'logo_url' => $bookmaker->logo_url,
                    'description' => $bookmaker->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $bookmakers,
            'count' => $bookmakers->count(),
        ]);
    }

    /**
     * Enregistrer un clic sur un bookmaker
     * 
     * POST /api/bookmakers/{id}/click
     * Body: {
     *   "prediction_id": "integer (optionnel)"
     * }
     */
    public function trackClick(Request $request, int $id): JsonResponse
    {
        $bookmaker = Bookmaker::active()->find($id);

        if (!$bookmaker) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmaker non trouvé ou inactif.',
            ], 404);
        }

        // Récupérer l'utilisateur (peut être null si mode invité)
        $user = $request->user();

        // Enregistrer le clic
        BookmakerClick::create([
            'user_id' => $user?->id,
            'bookmaker_id' => $bookmaker->id,
            'prediction_id' => $request->input('prediction_id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'clicked_at' => now(),
        ]);

        Log::info('Bookmaker click tracked', [
            'bookmaker_id' => $bookmaker->id,
            'user_id' => $user?->id,
            'prediction_id' => $request->input('prediction_id'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clic enregistré.',
            'data' => [
                'bookmaker' => [
                    'id' => $bookmaker->id,
                    'name' => $bookmaker->name,
                    'affiliate_link' => $bookmaker->affiliate_link,
                    'download_link' => $bookmaker->download_link,
                ],
            ],
        ]);
    }
}
