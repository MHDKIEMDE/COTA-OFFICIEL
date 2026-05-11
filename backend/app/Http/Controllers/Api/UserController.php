<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Contrôleur pour la gestion du profil utilisateur (RGPD)
 */
class UserController extends Controller
{
    /**
     * Récupérer le profil de l'utilisateur connecté
     * 
     * GET /api/user/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'is_premium' => $user->isPremium(),
                'premium_expires_at' => $user->premium_expires_at?->toIso8601String(),
                'premium_source' => $user->premium_source,
                'referral_code' => $user->referral_code,
                'referral_count' => $user->referral_count,
                'referral_days_earned' => $user->referral_days_earned,
                'can_access_welcome_combined' => $user->canAccessWelcomeCombined(),
                'created_at' => $user->created_at->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     * 
     * PUT /api/user/profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'country_code' => 'sometimes|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mettre à jour uniquement les champs fournis
        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }
        if ($request->has('country_code')) {
            $updateData['country_code'] = $request->country_code;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
            ],
        ]);
    }

    /**
     * Sauvegarder les préférences utilisateur (compétitions, équipe, fréquence)
     *
     * PUT /api/user/preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'competitions'    => 'sometimes|array',
            'competitions.*'  => 'string|max:100',
            'favorite_team'   => 'sometimes|nullable|string|max:100',
            'favorite_league' => 'sometimes|nullable|string|max:100',
            'frequency'       => 'sometimes|nullable|in:daily,important,manual',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $preferences = array_merge(
            $user->preferences ?? [],
            $request->only(['competitions', 'favorite_team', 'favorite_league', 'frequency'])
        );

        $user->update(['preferences' => $preferences]);

        return response()->json(['success' => true, 'message' => 'Préférences sauvegardées.', 'data' => $preferences]);
    }

    /**
     * Consulter les données stockées de l'utilisateur (RGPD - Droit d'accès)
     * 
     * GET /api/user/data-access
     */
    public function dataAccess(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Retourner un résumé des données stockées (sans les détails sensibles)
        $dataSummary = [
            'profile' => [
                'has_name' => !empty($user->name),
                'has_email' => !empty($user->email),
                'has_phone' => !empty($user->phone),
                'has_country_code' => !empty($user->country_code),
                'account_created_at' => $user->created_at->toIso8601String(),
                'last_updated_at' => $user->updated_at->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ],
            'premium' => [
                'is_premium' => $user->is_premium,
                'premium_expires_at' => $user->premium_expires_at?->toIso8601String(),
            ],
            'subscriptions_count' => DB::table('subscriptions')->where('user_id', $user->id)->count(),
            'referrals_count' => DB::table('referrals')->where('referrer_id', $user->id)->count(),
            'feedbacks_count' => DB::table('feedbacks')->where('user_id', $user->id)->count(),
            'favorites_count' => DB::table('user_favorites')->where('user_id', $user->id)->count(),
            'notification_settings' => $user->notification_settings ?? null,
            'data_categories' => [
                'profile' => 'Données de profil (nom, email, téléphone)',
                'premium' => 'Statut premium et abonnements',
                'referrals' => 'Données de parrainage',
                'feedbacks' => 'Feedbacks et réclamations',
                'favorites' => 'Favoris (matchs, compétitions)',
                'notifications' => 'Paramètres de notifications',
            ],
            'export_available' => true,
            'export_endpoint' => '/api/user/data-export',
            'deletion_available' => true,
            'deletion_endpoint' => '/api/user/data-delete',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Résumé des données stockées.',
            'data' => $dataSummary,
        ]);
    }

    /**
     * Exporter toutes les données de l'utilisateur (RGPD)
     * 
     * POST /api/user/data-export
     */
    public function exportData(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Récupérer toutes les données de l'utilisateur
        $userData = [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ],
            'premium' => [
                'is_premium' => $user->is_premium,
                'premium_expires_at' => $user->premium_expires_at?->toIso8601String(),
                'premium_source' => $user->premium_source,
            ],
            'referral' => [
                'referral_code' => $user->referral_code,
                'referral_count' => $user->referral_count,
                'referral_days_earned' => $user->referral_days_earned,
                'referred_by' => $user->referred_by,
            ],
            'subscriptions' => DB::table('subscriptions')
                ->where('user_id', $user->id)
                ->get()
                ->map(function ($subscription) {
                    return [
                        'id' => $subscription->id,
                        'plan' => $subscription->plan ?? null,
                        'amount' => $subscription->amount ?? null,
                        'paydunya_token' => $subscription->paydunya_token ?? null,
                        'status' => $subscription->status ?? null,
                        'expires_at' => $subscription->expires_at ?? null,
                        'created_at' => $subscription->created_at,
                        'updated_at' => $subscription->updated_at ?? null,
                    ];
                }),
            'referrals' => DB::table('referrals')
                ->where('referrer_user_id', $user->id)
                ->get()
                ->map(function ($referral) {
                    return [
                        'id' => $referral->id,
                        'referred_user_id' => $referral->referred_user_id,
                        'referral_code' => $referral->referral_code ?? null,
                        'status' => $referral->status,
                        'created_at' => $referral->created_at,
                        'updated_at' => $referral->updated_at ?? null,
                    ];
                }),
            'feedbacks' => DB::table('feedbacks')
                ->where('user_id', $user->id)
                ->get()
                ->map(function ($feedback) {
                    return [
                        'id' => $feedback->id,
                        'category' => $feedback->category ?? null,
                        'subject' => $feedback->subject ?? null,
                        'message' => $feedback->message ?? null,
                        'prediction_id' => $feedback->prediction_id ?? null,
                        'contest_reason' => $feedback->contest_reason ?? null,
                        'status' => $feedback->status ?? null,
                        'admin_response' => $feedback->admin_response ?? null,
                        'created_at' => $feedback->created_at,
                        'updated_at' => $feedback->updated_at ?? null,
                        'resolved_at' => $feedback->resolved_at ?? null,
                    ];
                }),
            'exported_at' => Carbon::now()->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Données exportées avec succès.',
            'data' => $userData,
        ]);
    }

    /**
     * Supprimer le compte utilisateur (RGPD)
     * 
     * DELETE /api/user/data-delete
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Vérifier la confirmation (optionnel, mais recommandé pour sécurité)
        $validator = Validator::make($request->all(), [
            'confirm' => 'required|boolean|accepted',
        ], [
            'confirm.accepted' => 'Vous devez confirmer la suppression de votre compte.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $user->id;

        // Supprimer toutes les données associées à l'utilisateur
        // Note: On utilise des suppressions en cascade ou on supprime manuellement
        
        // Supprimer les tokens Sanctum
        $user->tokens()->delete();

        // Supprimer les abonnements
        DB::table('subscriptions')->where('user_id', $userId)->delete();

        // Supprimer les références dans referrals
        DB::table('referrals')
            ->where('referrer_id', $userId)
            ->orWhere('referred_id', $userId)
            ->delete();

        // Supprimer les feedbacks
        DB::table('feedbacks')->where('user_id', $userId)->delete();

        // Supprimer les affiliations bonus
        DB::table('affiliations_bonus')->where('user_id', $userId)->delete();

        // Supprimer l'utilisateur
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Votre compte a été supprimé avec succès. Toutes vos données ont été effacées.',
        ]);
    }
}
