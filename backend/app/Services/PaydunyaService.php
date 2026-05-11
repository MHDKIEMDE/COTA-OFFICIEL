<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service d'intégration Paydunya pour Mobile Money
 *
 * Supporte : Wave, Orange Money, MTN Mobile Money, Moov Money
 * Documentation : https://paydunya.com/developers/v1/
 */
class PaydunyaService
{
    private ?string $masterKey;
    private ?string $privateKey;
    private ?string $token;
    private string $baseUrl;
    private bool $isTestMode;

    public function __construct()
    {
        $this->masterKey = config('services.paydunya.master_key') ?? '';
        $this->privateKey = config('services.paydunya.private_key') ?? '';
        $this->token = config('services.paydunya.token') ?? '';
        $this->isTestMode = config('services.paydunya.mode', 'test') === 'test';
        $this->baseUrl = $this->isTestMode
            ? 'https://app.paydunya.com/sandbox-api/v1'
            : 'https://app.paydunya.com/api/v1';
    }

    /**
     * Créer une nouvelle facture de paiement
     *
     * @param array $data [
     *   'amount' => float,
     *   'description' => string,
     *   'user_id' => int,
     *   'user_email' => string,
     *   'user_name' => string,
     *   'user_phone' => string,
     *   'plan' => 'weekly|monthly|quarterly'
     * ]
     * @return array ['success' => bool, 'response_code' => string, 'token' => string, 'url' => string]
     */
    public function createInvoice(array $data): array
    {
        try {
            $invoice = [
                'invoice' => [
                    'total_amount' => $data['amount'],
                    'description' => $data['description'],
                ],
                'store' => [
                    'name' => config('app.name', 'COTA'),
                    'tagline' => 'Pronostics Football Automatisés',
                    'postal_address' => 'Ouagadougou, Burkina Faso',
                    'phone' => '+226 00 00 00 00',
                    'logo_url' => config('app.url') . '/logo.png',
                    'website_url' => config('app.url'),
                ],
                'actions' => [
                    'cancel_url' => config('app.frontend_url') . '/subscription/cancel',
                    'return_url' => config('app.frontend_url') . '/subscription/success',
                    'callback_url' => config('app.url') . '/api/webhooks/paydunya',
                ],
                'custom_data' => [
                    'user_id' => $data['user_id'],
                    'plan' => $data['plan'],
                    'app' => 'cota',
                ],
            ];

            // Ajouter les informations client si disponibles
            if (!empty($data['user_name'])) {
                $invoice['customer'] = [
                    'name' => $data['user_name'],
                    'email' => $data['user_email'] ?? '',
                    'phone' => $data['user_phone'] ?? '',
                ];
            }

            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/checkout-invoice/create", $invoice);

            if ($response->successful()) {
                $result = $response->json();

                Log::info('Paydunya invoice created', [
                    'user_id' => $data['user_id'],
                    'amount' => $data['amount'],
                    'token' => $result['token'] ?? null,
                ]);

                return [
                    'success' => true,
                    'response_code' => $result['response_code'] ?? '00',
                    'token' => $result['token'] ?? null,
                    'url' => $result['response_text'] ?? null, // URL de paiement
                    'raw_response' => $result,
                ];
            }

            Log::error('Paydunya invoice creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Échec de création de facture',
                'error' => $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('Paydunya invoice exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la facture',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     *
     * @param string $token Token de la facture
     * @return array ['success' => bool, 'status' => string, 'custom_data' => array]
     */
    public function checkPaymentStatus(string $token): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/checkout-invoice/confirm/{$token}");

            if ($response->successful()) {
                $result = $response->json();

                $status = $result['status'] ?? 'pending';
                $isCompleted = in_array($status, ['completed', 'success']);

                Log::info('Paydunya payment status checked', [
                    'token' => $token,
                    'status' => $status,
                    'completed' => $isCompleted,
                ]);

                return [
                    'success' => true,
                    'status' => $status,
                    'is_completed' => $isCompleted,
                    'custom_data' => $result['custom_data'] ?? [],
                    'invoice' => $result['invoice'] ?? [],
                    'raw_response' => $result,
                ];
            }

            return [
                'success' => false,
                'status' => 'error',
                'message' => 'Impossible de vérifier le statut',
            ];

        } catch (Exception $e) {
            Log::error('Paydunya status check exception', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier la signature du webhook
     *
     * @param array $data Données reçues du webhook
     * @param string $signature Signature reçue dans les headers
     * @return bool
     */
    public function verifyWebhookSignature(array $data, string $signature): bool
    {
        $computedSignature = hash_hmac('sha512', json_encode($data), $this->privateKey);
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Calculer la date d'expiration selon le plan
     *
     * @param string $plan 'weekly', 'monthly', 'quarterly'
     * @return \Carbon\Carbon
     */
    public function calculateExpirationDate(string $plan): \Carbon\Carbon
    {
        return match($plan) {
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            default => now()->addWeek(),
        };
    }

    /**
     * Obtenir le prix selon le plan
     *
     * @param string $plan 'weekly', 'monthly', 'quarterly'
     * @return int Prix en FCFA
     */
    public function getPlanPrice(string $plan): int
    {
        return match($plan) {
            'weekly' => 2500,
            'monthly' => 8000,
            'quarterly' => 20000,
            default => 2500,
        };
    }

    /**
     * Headers HTTP pour les requêtes Paydunya
     *
     * @return array
     */
    private function getHeaders(): array
    {
        return [
            'PAYDUNYA-MASTER-KEY' => $this->masterKey,
            'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
            'PAYDUNYA-TOKEN' => $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Tester la connexion à l'API Paydunya
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/setup");

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Paydunya connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
