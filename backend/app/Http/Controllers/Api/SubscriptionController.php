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
            'user_id'        => $user->id,
            'plan'           => $plan,
            'amount'         => $amount,
            'paydunya_token' => $result['token'],
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
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
     * @param Request $request
     * @return JsonResponse
     *
     * GET /api/subscriptions/verify/{token}
     */
    public function verifyPayment(Request $request, string $token): JsonResponse
    {
        $user = $request->user();

        // Vérifier le statut du paiement
        $result = $this->paydunya->checkPaymentStatus($token);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
                'error' => $result['message'] ?? 'Erreur inconnue',
            ], 500);
        }

        $status = $result['status'];
        $isCompleted = $result['is_completed'];

        // Si le paiement est complété, activer le premium
        if ($isCompleted) {
            $customData = $result['custom_data'];
            $plan = $customData['plan'] ?? 'weekly';
            $userId = $customData['user_id'] ?? $user->id;

            // Vérifier que c'est bien le bon utilisateur
            if ($userId != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de paiement invalide pour cet utilisateur',
                ], 403);
            }

            // Activer le premium
            $this->activatePremium($user, $plan, $token);

            return response()->json([
                'success' => true,
                'message' => 'Paiement confirmé ! Votre abonnement premium est activé.',
                'data' => [
                    'is_premium' => true,
                    'subscription_expires_at' => $user->fresh()->subscription_expires_at,
                    'plan' => $plan,
                ],
            ]);
        }

        // Paiement en attente ou échoué
        return response()->json([
            'success' => false,
            'message' => 'Paiement non complété',
            'data' => [
                'status' => $status,
                'is_completed' => false,
            ],
        ]);
    }

    /**
     * Webhook Paydunya pour les notifications de paiement
     *
     * @param Request $request
     * @return JsonResponse
     *
     * POST /api/webhooks/paydunya
     */
    public function webhook(Request $request): JsonResponse
    {
        // Récupérer la signature du header
        $signature = $request->header('X-Paydunya-Signature', '');

        // Vérifier la signature
        if (!$this->paydunya->verifyWebhookSignature($request->all(), $signature)) {
            Log::warning('Paydunya webhook signature invalid', [
                'ip' => $request->ip(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data = $request->all();
        $status = $data['status'] ?? 'unknown';
        $token = $data['invoice']['token'] ?? null;

        Log::info('Paydunya webhook received', [
            'status' => $status,
            'token' => $token,
            'data' => $data,
        ]);

        // Si paiement complété
        if (in_array($status, ['completed', 'success']) && $token) {
            $customData = $data['custom_data'] ?? [];
            $userId = $customData['user_id'] ?? null;
            $plan = $customData['plan'] ?? 'weekly';

            if ($userId) {
                $user = User::find($userId);

                if ($user) {
                    $this->activatePremium($user, $plan, $token);

                    Log::info('Premium activated via webhook', [
                        'user_id' => $userId,
                        'plan' => $plan,
                        'token' => $token,
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
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
        $plans = [
            [
                'id' => 'weekly',
                'name' => 'Hebdomadaire',
                'duration' => '7 jours',
                'price' => 2500,
                'currency' => 'FCFA',
                'features' => [
                    'Tous les pronostics quotidiens',
                    'Combinés premium',
                    'Statistiques détaillées',
                    'Support prioritaire',
                ],
            ],
            [
                'id' => 'monthly',
                'name' => 'Mensuel',
                'duration' => '30 jours',
                'price' => 8000,
                'currency' => 'FCFA',
                'savings' => '20%',
                'features' => [
                    'Tous les pronostics quotidiens',
                    'Combinés premium',
                    'Statistiques détaillées',
                    'Support prioritaire',
                    'Historique complet',
                ],
                'recommended' => true,
            ],
            [
                'id' => 'quarterly',
                'name' => 'Trimestriel',
                'duration' => '90 jours',
                'price' => 20000,
                'currency' => 'FCFA',
                'savings' => '33%',
                'features' => [
                    'Tous les pronostics quotidiens',
                    'Combinés premium',
                    'Statistiques détaillées',
                    'Support prioritaire',
                    'Historique complet',
                    'Analyses avancées',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
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
        $expirationDate = $this->paydunya->calculateExpirationDate($plan);

        // Si l'utilisateur a déjà un abonnement actif, ajouter à la date existante
        if ($user->is_premium && $user->subscription_expires_at > now()) {
            $expirationDate = Carbon::parse($user->subscription_expires_at)
                ->add($this->getDuration($plan));
        }

        $user->update([
            'is_premium' => true,
            'subscription_expires_at' => $expirationDate,
        ]);

        // Mettre à jour le statut de la subscription en base
        DB::table('subscriptions')
            ->where('paydunya_token', $token)
            ->update([
                'status' => 'completed',
                'expires_at' => $expirationDate,
                'updated_at' => now(),
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
