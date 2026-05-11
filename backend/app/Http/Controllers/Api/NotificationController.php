<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour la gestion des notifications push (FCM)
 */
class NotificationController extends Controller
{
    /**
     * Enregistrer le token FCM de l'utilisateur
     * 
     * POST /api/notifications/register
     * Body: {
     *   "fcm_token": "string",
     *   "device_type": "ios|android" (optionnel)
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:500',
            'device_type' => 'nullable|string|in:ios,android',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mettre à jour le token FCM de l'utilisateur
        $user->update([
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type ?? $user->device_type,
        ]);

        Log::info('FCM token registered', [
            'user_id' => $user->id,
            'device_type' => $user->device_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token FCM enregistré avec succès.',
            'data' => [
                'fcm_token_registered' => true,
                'device_type' => $user->device_type,
            ],
        ]);
    }

    /**
     * Récupérer les paramètres de notifications de l'utilisateur
     * 
     * GET /api/notifications/settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Pour l'instant, on stocke les settings dans une colonne JSON de users
        // Si la colonne n'existe pas encore, on retourne des valeurs par défaut
        $settings = $user->notification_settings ?? [
            'push_enabled' => true,
            'predictions_enabled' => true,
            'live_scores_enabled' => true,
            'goals_enabled' => true,
            'combined_enabled' => true,
            'promotions_enabled' => true,
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Mettre à jour les paramètres de notifications
     * 
     * PUT /api/notifications/settings
     * Body: {
     *   "push_enabled": true|false,
     *   "predictions_enabled": true|false,
     *   "live_scores_enabled": true|false,
     *   "goals_enabled": true|false,
     *   "combined_enabled": true|false,
     *   "promotions_enabled": true|false
     * }
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'push_enabled' => 'sometimes|boolean',
            'predictions_enabled' => 'sometimes|boolean',
            'live_scores_enabled' => 'sometimes|boolean',
            'goals_enabled' => 'sometimes|boolean',
            'combined_enabled' => 'sometimes|boolean',
            'promotions_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer les settings actuels
        $currentSettings = $user->notification_settings ?? [];
        
        // Fusionner avec les nouvelles valeurs
        $newSettings = array_merge($currentSettings, $request->only([
            'push_enabled',
            'predictions_enabled',
            'live_scores_enabled',
            'goals_enabled',
            'combined_enabled',
            'promotions_enabled',
        ]));

        // Mettre à jour dans la base de données
        // Note: Si la colonne notification_settings n'existe pas encore, il faudra créer une migration
        // Pour l'instant, on utilise un champ JSON dans users
        $user->update([
            'notification_settings' => $newSettings,
        ]);

        Log::info('Notification settings updated', [
            'user_id' => $user->id,
            'settings' => $newSettings,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paramètres de notifications mis à jour.',
            'data' => $newSettings,
        ]);
    }

    /**
     * Désinscrire le token FCM (supprimer le token)
     * 
     * DELETE /api/notifications/unregister
     */
    public function unregister(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $user->update([
            'fcm_token' => null,
        ]);

        Log::info('FCM token unregistered', [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token FCM supprimé avec succès.',
        ]);
    }
}
