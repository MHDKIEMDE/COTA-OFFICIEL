<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\User;
use App\Services\Payment\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * POST /api/webhooks/payment
     * Point d'entrée unique pour tous les providers de paiement.
     */
    public function handle(Request $request, PaymentGatewayService $gateway): Response
    {
        $rawBody = $request->getContent();
        $payload = $request->all();
        $headers = $request->headers->all();

        Log::info('Webhook paiement reçu', [
            'provider' => $gateway->activeProvider(),
            'payload'  => $payload,
        ]);

        try {
            $normalized = $gateway->gateway()->parseWebhook($payload, $rawBody, $headers);
        } catch (\Throwable $e) {
            Log::error('Webhook: impossible de résoudre le gateway', ['error' => $e->getMessage()]);
            return response('gateway_error', 200);
        }

        if (!$normalized) {
            Log::warning('Webhook: payload non parseable', ['payload' => $payload]);
            return response('invalid_payload', 200);
        }

        ['token' => $token, 'status' => $status, 'amount' => $amount] = $normalized;

        if ($status !== 'completed') {
            DB::table('subscriptions')
                ->where('paydunya_token', $token)
                ->update(['status' => $status, 'updated_at' => now()]);

            return response('ok', 200);
        }

        // Récupérer la subscription en attente
        $subscription = DB::table('subscriptions')
            ->where('paydunya_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$subscription) {
            Log::info('Webhook: subscription non trouvée ou déjà traitée', ['token' => $token]);
            return response('already_processed', 200);
        }

        // Activer le premium
        $plans    = AppConfig::get('app.premium_plans', []);
        $days     = (int) ($plans[$subscription->plan]['days'] ?? 30);
        $expireAt = Carbon::now()->addDays($days);

        DB::transaction(function () use ($subscription, $expireAt, $status) {
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['status' => 'completed', 'updated_at' => now()]);

            User::where('id', $subscription->user_id)->update([
                'is_premium'              => true,
                'subscription_expires_at' => $expireAt,
            ]);
        });

        Log::info('Premium activé via webhook', [
            'user_id'    => $subscription->user_id,
            'plan'       => $subscription->plan,
            'expires_at' => $expireAt,
        ]);

        return response('ok', 200);
    }
}
