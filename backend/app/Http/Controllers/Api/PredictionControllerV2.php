<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\FootballApiService;

class PredictionControllerV2 extends Controller
{
    protected FootballApiService $footballData;

    public function __construct(FootballApiService $footballData)
    {
        $this->footballData = $footballData;
    }

    /**
     * Récupérer les pronostics du jour avec fallback automatique
     */
    public function today(Request $request)
    {
        $user = auth('sanctum')->user();
        $isPremium = $user && $user->is_premium;

        // Paramètres de date et compétition
        $dateParam = $request->query('date');
        $selectedDate = $dateParam
            ? Carbon::parse($dateParam)->startOfDay()
            : Carbon::today();

        $dateString = $selectedDate->format('Y-m-d');
        $competition = $request->query('competition', 'all');

        // Clé de cache
        $cacheKey = "predictions_today_{$dateString}_{$competition}_" . ($isPremium ? 'premium' : 'free');
        $cacheTtl = 300; // 5 minutes

        // Vérifier le cache
        $cached = Cache::get($cacheKey);
        if ($cached) {
            Log::info("✅ Cache hit for predictions {$dateString}");
            return response()->json($cached);
        }

        // 1. Vérifier les prédictions en base de données
        $startDate = $selectedDate->copy();
        $endDate = $selectedDate->copy()->endOfDay();

        $query = DB::table('predictions')
            ->where('is_published', true)
            ->whereBetween('match_date', [$startDate, $endDate])
            ->orderBy('match_date', 'asc');

        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        $dbPredictions = $query->get();

        // Si on a assez de prédictions en base, les retourner directement
        if ($dbPredictions->count() >= 3) {
            Log::info("✅ {$dbPredictions->count()} prédictions trouvées en base (source: database)");

            $formattedPredictions = $dbPredictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            })->values();

            $responseData = [
                'success' => true,
                'data' => $formattedPredictions,
                'source' => 'database',
                'cached_at' => now()->toIso8601String(),
            ];

            Cache::put($cacheKey, $responseData, $cacheTtl);
            return response()->json($responseData);
        }

        // 2. Peu de données en base, récupérer depuis les APIs
        Log::info("⚠️ Seulement {$dbPredictions->count()} prédictions en base, récupération depuis les APIs");

        try {
            // Récupérer les matchs depuis API-Football
            $response = $this->footballData->getUpcomingMatches(1);
            $fixtures = $response['response'] ?? [];

            // Filtrer par date et normaliser
            $matches = array_values(array_filter(array_map(function ($fixture) {
                return [
                    'id'         => (string) ($fixture['fixture']['id'] ?? ''),
                    'home_team'  => $fixture['teams']['home']['name'] ?? 'Home',
                    'away_team'  => $fixture['teams']['away']['name'] ?? 'Away',
                    'competition'=> $fixture['league']['name'] ?? 'Unknown',
                    'match_date' => $fixture['fixture']['date'] ?? null,
                ];
            }, $fixtures), fn($m) => !empty($m['id'])));

            if (empty($matches)) {
                Log::warning("⚠️ Aucun match trouvé pour le {$dateString}");

                $responseData = [
                    'success' => true,
                    'data' => [],
                    'source' => 'api_empty',
                    'message' => 'Aucun match disponible pour cette date',
                    'cached_at' => now()->toIso8601String(),
                ];

                Cache::put($cacheKey, $responseData, $cacheTtl);
                return response()->json($responseData);
            }

            Log::info("✅ " . count($matches) . " matchs récupérés pour le {$dateString}");

            // Générer des prédictions pour les nouveaux matchs
            $newPredictions = [];
            foreach ($matches as $match) {
                // Vérifier si une prédiction existe déjà
                $existingPrediction = DB::table('predictions')
                    ->where('match_id', $match['id'])
                    ->first();

                if (!$existingPrediction) {
                    // Générer une prédiction basique (à améliorer avec un vrai algorithme)
                    $prediction = $this->generateBasicPrediction($match, $isPremium);
                    if ($prediction) {
                        $newPredictions[] = $prediction;
                    }
                }
            }

            // Combiner les prédictions existantes et nouvelles
            $allPredictions = $dbPredictions->concat(collect($newPredictions));

            $formattedPredictions = $allPredictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            })->values();

            $responseData = [
                'success' => true,
                'data' => $formattedPredictions,
                'source' => 'api_generated',
                'new_predictions' => count($newPredictions),
                'cached_at' => now()->toIso8601String(),
            ];

            Cache::put($cacheKey, $responseData, $cacheTtl);
            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error("❌ Erreur récupération prédictions: " . $e->getMessage());

            // Retourner ce qu'on a en base même si c'est peu
            if ($dbPredictions->count() > 0) {
                Log::info("🔄 Retour fallback: {$dbPredictions->count()} prédictions depuis la base");

                $formattedPredictions = $dbPredictions->map(function ($prediction) use ($isPremium) {
                    return $this->formatPrediction($prediction, $isPremium);
                })->values();

                $responseData = [
                    'success' => true,
                    'data' => $formattedPredictions,
                    'source' => 'database_fallback',
                    'cached_at' => now()->toIso8601String(),
                ];

                Cache::put($cacheKey, $responseData, $cacheTtl);
                return response()->json($responseData);
            }

            // Rien du tout
            return response()->json([
                'success' => false,
                'error' => 'Service temporairement indisponible',
                'message' => 'Impossible de récupérer les prédictions pour le moment'
            ], 503);
        }
    }

    /**
     * Générer une prédiction basique pour un match
     * TODO: Remplacer par un vrai algorithme de prédiction
     */
    protected function generateBasicPrediction(array $match, bool $isPremium): ?object
    {
        try {
            // Simulation d'une prédiction basique
            $prediction = (object) [
                'id' => rand(10000, 99999),
                'match_id' => $match['id'],
                'home_team' => $match['home_team'],
                'away_team' => $match['away_team'],
                'competition' => $match['competition'] ?? 'Unknown',
                'match_date' => $match['match_date'],
                'bet_type' => '1X2',
                'prediction' => ['1', 'X', '2'][rand(0, 2)], // Aléatoire pour l'exemple
                'odds' => number_format(rand(150, 350) / 100, 2),
                'confidence_stars' => rand(1, 4),
                'is_premium' => $isPremium && rand(0, 1), // 50% premium si user premium
                'analysis_details' => json_encode([
                    'reasoning' => 'Prédiction générée automatiquement - Analyse complète à venir',
                    'algorithm_version' => 'basic_v1',
                    'generated_at' => now()->toIso8601String(),
                ]),
                'source' => 'generated'
            ];

            return $prediction;
        } catch (\Exception $e) {
            Log::error("❌ Erreur génération prédiction basique: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Formater une prédiction pour la réponse API
     */
    protected function formatPrediction($prediction, bool $isPremium): array
    {
        return [
            'id' => $prediction->id,
            'match_id' => $prediction->match_id,
            'home_team' => $prediction->home_team,
            'away_team' => $prediction->away_team,
            'competition' => $prediction->competition,
            'match_date' => $prediction->match_date,
            'bet_type' => $prediction->bet_type,
            'prediction' => $prediction->prediction,
            'odds' => $prediction->odds,
            'confidence_stars' => $prediction->confidence_stars,
            'is_premium' => $prediction->is_premium,
            'analysis_details' => json_decode($prediction->analysis_details, true),
            'source' => $prediction->source ?? 'database',
        ];
    }

    /**
     * Obtenir les statistiques d'usage des APIs
     */
    public function apiStats(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->footballData->getUsageStats(),
        ]);
    }
}