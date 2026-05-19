<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour la gestion des favoris utilisateur
 * 
 * Types supportés: 'team', 'competition', 'match'
 */
class FavoriteController extends Controller
{
    /**
     * Récupérer tous les favoris de l'utilisateur
     * 
     * GET /api/favorites
     * Query params: ?type=team|competition|match (optionnel)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $type     = $request->query('type');
        $cacheKey = "favorites:user:{$user->id}" . ($type ? ":{$type}" : '');

        $favorites = Cache::remember($cacheKey, 120, function () use ($user, $type) {
            $query = UserFavorite::forUser($user->id);

            if ($type && in_array($type, ['team', 'competition', 'match'])) {
                $query->ofType($type);
            }

            return $query->orderBy('created_at', 'desc')->get()->map(fn($f) => [
                'id'           => $f->id,
                'type'         => $f->type,
                'item_id'      => $f->item_id,
                'item_name'    => $f->item_name,
                'item_logo'    => $f->item_logo,
                'item_country' => $f->item_country,
                'created_at'   => $f->created_at->toIso8601String(),
            ]);
        });

        return response()->json([
            'success' => true,
            'data'    => $favorites,
            'count'   => $favorites->count(),
        ]);
    }

    /**
     * Ajouter un favori
     * 
     * POST /api/favorites
     * Body: {
     *   "type": "team|competition|match",
     *   "item_id": "string",
     *   "item_name": "string (optionnel)",
     *   "item_logo": "string (optionnel)",
     *   "item_country": "string (optionnel)"
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:team,competition,match',
            'item_id' => 'required|string|max:255',
            'item_name' => 'nullable|string|max:255',
            'item_logo' => 'nullable|string|max:500',
            'item_country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Vérifier si le favori existe déjà
        if (UserFavorite::exists($user->id, $request->type, $request->item_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce favori existe déjà.',
            ], 409);
        }

        // Créer le favori
        $favorite = UserFavorite::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'item_id' => $request->item_id,
            'item_name' => $request->item_name,
            'item_logo' => $request->item_logo,
            'item_country' => $request->item_country,
        ]);

        Cache::forget("favorites:user:{$user->id}");
        Cache::forget("favorites:user:{$user->id}:{$request->type}");

        Log::info('Favorite added', [
            'user_id' => $user->id,
            'type' => $request->type,
            'item_id' => $request->item_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favori ajouté avec succès.',
            'data' => [
                'id' => $favorite->id,
                'type' => $favorite->type,
                'item_id' => $favorite->item_id,
                'item_name' => $favorite->item_name,
                'item_logo' => $favorite->item_logo,
                'item_country' => $favorite->item_country,
                'created_at' => $favorite->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Supprimer un favori
     * 
     * DELETE /api/favorites/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $favorite = UserFavorite::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favori non trouvé.',
            ], 404);
        }

        $type = $favorite->type;
        $favorite->delete();

        Cache::forget("favorites:user:{$user->id}");
        Cache::forget("favorites:user:{$user->id}:{$type}");

        Log::info('Favorite removed', [
            'user_id' => $user->id,
            'favorite_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favori supprimé avec succès.',
        ]);
    }

    /**
     * Supprimer un favori par type et item_id
     * 
     * DELETE /api/favorites
     * Body: {
     *   "type": "team|competition|match",
     *   "item_id": "string"
     * }
     */
    public function destroyByItem(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:team,competition,match',
            'item_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $favorite = UserFavorite::where('user_id', $user->id)
            ->where('type', $request->type)
            ->where('item_id', $request->item_id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favori non trouvé.',
            ], 404);
        }

        $favorite->delete();

        Cache::forget("favorites:user:{$user->id}");
        Cache::forget("favorites:user:{$user->id}:{$request->type}");

        Log::info('Favorite removed by item', [
            'user_id' => $user->id,
            'type' => $request->type,
            'item_id' => $request->item_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favori supprimé avec succès.',
        ]);
    }

    /**
     * Vérifier si un élément est en favori
     * 
     * GET /api/favorites/check
     * Query params: ?type=team|competition|match&item_id=string
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:team,competition,match',
            'item_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = UserFavorite::exists($user->id, $request->type, $request->item_id);

        return response()->json([
            'success' => true,
            'is_favorite' => $exists,
        ]);
    }
}
