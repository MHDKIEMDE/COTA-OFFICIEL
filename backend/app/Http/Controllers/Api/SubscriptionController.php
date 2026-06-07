<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\User;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function __construct(private PaymentGatewayService $gateway) {}


    /**
     * Initier un achat d'abonnement premium
     *
     * @param Request $request
     * @return JsonResponse
     *
     * POST /api/subscriptions/purchase
     * Body: {
     *   "plan": "weekly|monthly|quarterly"
     * }
     */
    public function initiatePurchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|in:weekly,monthly,quarterly',
        ]);

        $user = $request->user();
        $plan = $validated['plan'];

        try {
            $gw     = $this->gateway->gateway();
            $amount = $gw->getPlanPrice($plan);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement temporairement indisponible.',
                'error'   => $e->getMessage(),
            ], 503);
        }

        $plans       = AppConfig::get('app.premium_plans', []);
        $description = ($plans[$plan]['label'] ?? $plan) . ' — COTA Premium';

        $result = $gw->createInvoice([
            'amount'      => $amount,
            'description' => $description,
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'user_name'   => $user->name ?? 'Utilisateur COTA',
            'user_phone'  => $user->phone,
            'plan'        => $plan,
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de créer la facture de paiement.',
                'error'   => $result['error'] ?? 'Erreur inconnue',
            ], 500);
        }

        DB::table('subscriptions')->insert([
            'user_id'          => $user->id,
            'plan'             => $plan,
            'amount'           => $amount,
            'payment_token'    => $result['token'],
            'payment_provider' => $this->gateway->activeProvider(),
            'payment_url'      => $result['payment_url'],
            'payment_status'   => 'pending',
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        Log::info('Subscription initiated', [
            'provider' => $this->gateway->activeProvider(),
            'user_id'  => $user->id,
            'plan'     => $plan,
            'token'    => $result['token'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Facture créée avec succès.',
            'data'    => [
                'payment_url' => $result['payment_url'],
                'token'       => $result['token'],
                'amount'      => $amount,
                'plan'        => $plan,
                'provider'    => $this->gateway->activeProvider(),
            ],
        ]);
    }

    /**
     * Vérifier le statut d'un paiement
     *
     * GET /api/subscriptions/verify/{token}
     */
    public function verifyPayment(Request $request, string $token): JsonResponse
    {
        $user = $request->user();

        try {
            $result = $this->gateway->gateway()->verifyTransaction($token);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement temporairement indisponible.',
                'error'   => $e->getMessage(),
            ], 503);
        }

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
                'error'   => $result['error'] ?? 'Erreur inconnue',
            ], 500);
        }

        if ($result['status'] !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non complété',
                'data'    => ['status' => $result['status'], 'is_completed' => false],
            ]);
        }

        $customData = $result['data']['custom_data'] ?? [];
        $plan       = $customData['plan'] ?? 'weekly';
        $userId     = $customData['user_id'] ?? $user->id;

        if ((int) $userId !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token de paiement invalide pour cet utilisateur',
            ], 403);
        }

        $this->activatePremium($user, $plan, $token);

        return response()->json([
            'success' => true,
            'message' => 'Paiement confirmé ! Votre abonnement premium est activé.',
            'data'    => [
                'is_premium'              => true,
                'subscription_expires_at' => $user->fresh()->subscription_expires_at,
                'plan'                    => $plan,
            ],
        ]);
    }

    /**
     * Obtenir les informations d'abonnement de l'utilisateur
     *
     * @param Request $request
     * @return JsonResponse
     *
     * GET /api/subscriptions/me
     */
    public function getMySubscription(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'is_premium' => $user->is_premium,
                'subscription_expires_at' => $user->subscription_expires_at,
                'days_remaining' => $user->subscription_expires_at
                    ? now()->diffInDays($user->subscription_expires_at, false)
                    : 0,
            ],
        ]);
    }

    /**
     * Obtenir les plans tarifaires disponibles
     *
     * @return JsonResponse
     *
     * GET /api/subscriptions/plans
     */
    public function getPlans(): JsonResponse
    {
        $default = [
            'weekly' => [
                'id'          => 'weekly',
                'name'        => 'Hebdo',
                'duration'    => '7 jours',
                'price'       => 2500,
                'currency'    => 'FCFA',
                'badge'       => null,
                'recommended' => false,
                'features'    => [
                    ['title' => 'Pronostics 3–4 étoiles',   'description' => 'Accès aux prédictions premium pendant 7 jours'],
                    ['title' => 'Coupon combiné quotidien',  'description' => '4–5 meilleurs picks combinés chaque jour'],
                    ['title' => 'Alertes push',              'description' => 'Notifications en temps réel sur vos matchs'],
                ],
            ],
            'monthly' => [
                'id'          => 'monthly',
                'name'        => 'Mensuel',
                'duration'    => '30 jours',
                'price'       => 8000,
                'currency'    => 'FCFA',
                'badge'       => 'Populaire',
                'recommended' => true,
                'features'    => [
                    ['title' => 'Pronostics 3–4 étoiles illimités', 'description' => 'Accès complet à toutes les prédictions premium'],
                    ['title' => 'Coupon combiné quotidien',          'description' => '4–5 meilleurs picks combinés chaque jour'],
                    ['title' => 'Analyses détaillées',               'description' => 'Critères, cotes estimées, historique complet'],
                    ['title' => 'Alertes push',                      'description' => 'Notifications en temps réel sur vos matchs'],
                    ['title' => 'Historique 30 jours',               'description' => 'Toutes vos prédictions et résultats du mois'],
                ],
            ],
            'quarterly' => [
                'id'          => 'quarterly',
                'name'        => 'Trimestriel',
                'duration'    => '90 jours',
                'price'       => 20000,
                'currency'    => 'FCFA',
                'badge'       => '-17%',
                'recommended' => false,
                'features'    => [
                    ['title' => 'Pronostics 3–4 étoiles illimités',       'description' => 'Accès complet à toutes les prédictions premium'],
                    ['title' => 'Coupon combiné quotidien',                'description' => '4–5 meilleurs picks combinés chaque jour'],
                    ['title' => 'Analyses détaillées',                     'description' => 'Critères, cotes estimées, historique complet'],
                    ['title' => 'Alertes push prioritaires',               'description' => 'Notifications en temps réel + alertes VIP'],
                    ['title' => 'Historique illimité',                     'description' => 'Toutes les prédictions passées et résultats'],
                    ['title' => 'Accès prioritaire nouvelles features',    'description' => 'Fonctionnalités bêta en avant-première'],
                ],
            ],
        ];

        $overrides = AppConfig::get('app.premium_plans', []);
        if (!empty($overrides)) {
            foreach ($overrides as $plan) {
                $id = $plan['id'] ?? null;
                if ($id && isset($default[$id])) {
                    $default[$id] = array_merge($default[$id], $plan);
                }
            }
        }

        return response()->json(['success' => true, 'data' => array_values($default)]);
    }

    /**
     * Activer le premium pour un utilisateur
     *
     * @param User $user
     * @param string $plan
     * @param string $token
     * @return void
     */
    private function activatePremium(User $user, string $plan, string $token): void
    {
        $duration       = $this->getDuration($plan);
        $expirationDate = now()->add($duration);

        if ($user->is_premium && $user->subscription_expires_at > now()) {
            $expirationDate = Carbon::parse($user->subscription_expires_at)->add($duration);
        }

        $user->update([
            'is_premium' => true,
            'subscription_expires_at' => $expirationDate,
        ]);

        // Mettre à jour le statut de la subscription en base
        DB::table('subscriptions')
            ->where('payment_token', $token)
            ->update([
                'status'         => 'active',
                'payment_status' => 'completed',
                'expires_at'     => $expirationDate,
                'updated_at'     => now(),
            ]);

        Log::info('Premium activated', [
            'user_id' => $user->id,
            'plan' => $plan,
            'expires_at' => $expirationDate,
        ]);
    }

    /**
     * Obtenir la durée selon le plan
     *
     * @param string $plan
     * @return \DateInterval
     */
    private function getDuration(string $plan): \DateInterval
    {
        return match($plan) {
            'weekly' => new \DateInterval('P7D'),
            'monthly' => new \DateInterval('P30D'),
            'quarterly' => new \DateInterval('P90D'),
            default => new \DateInterval('P7D'),
        };
    }
}
