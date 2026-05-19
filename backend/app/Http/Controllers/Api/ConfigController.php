<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Controller pour la configuration dynamique de l'application
 * 
 * Permet à l'admin de modifier la config sans toucher au code
 */
class ConfigController extends Controller
{
    /**
     * Récupérer toutes les configurations de l'application
     * 
     * GET /api/config/app
     * Accessible sans authentification (config publique)
     */
    public function getAppConfig(): JsonResponse
    {
        $configs = Cache::remember('app:config', 600, fn() => AppConfig::allAsArray());

        // Valeurs par défaut si aucune config n'existe
        $defaultConfigs = [
            'app_name' => 'COTA',
            'app_slogan' => 'Prédis, Suis, Gagne',
            'primary_color' => '#00CED1',
            'background_color' => '#1a1a2e',
            'welcome_message' => 'Bienvenue sur COTA !',
            'cgu_url' => null,
            'privacy_policy_url' => null,
            'play_store_url' => null,
            'app_store_url' => null,
            'promo_code' => 'CMD1122',
        ];

        // Fusionner avec les configs de la base
        $mergedConfigs = array_merge($defaultConfigs, $configs);

        return response()->json([
            'success' => true,
            'data' => $mergedConfigs,
        ]);
    }
}
