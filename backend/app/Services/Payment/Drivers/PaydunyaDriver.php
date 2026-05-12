<?php

namespace App\Services\Payment\Drivers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaydunyaDriver implements PaymentGatewayInterface
{
    private string $masterKey;
    private string $privateKey;
    private string $token;
    private string $baseUrl;

    public function __construct(array $config)
    {
        $isTest       = ($config['env'] ?? 'test') === 'test';
        $this->masterKey  = $config['api_key']    ?? '';
        $this->privateKey = $config['api_secret'] ?? '';
        $this->token      = $config['extra']['token'] ?? '';
        $this->baseUrl    = $isTest
            ? 'https://app.paydunya.com/sandbox-api/v1'
            : 'https://app.paydunya.com/api/v1';
    }

    public function createInvoice(array $data): array
    {
        try {
            $payload = [
                'invoice' => [
                    'total_amount' => $data['amount'],
                    'description'  => $data['description'],
                ],
                'store' => [
                    'name'         => config('app.name', 'COTA'),
                    'tagline'      => 'Pronostics Football',
                    'website_url'  => config('app.url'),
                    'callback_url' => config('app.url') . '/api/webhooks/payment',
                ],
                'actions' => [
                    'cancel_url'   => config('app.frontend_url', config('app.url')) . '/subscription/cancel',
                    'return_url'   => config('app.frontend_url', config('app.url')) . '/subscription/success',
                    'callback_url' => config('app.url') . '/api/webhooks/payment',
                ],
                'custom_data' => [
                    'user_id' => $data['user_id'],
                    'plan'    => $data['plan'],
                ],
            ];

            if (!empty($data['user_name'])) {
                $payload['customer'] = [
                    'name'  => $data['user_name'],
                    'email' => $data['user_email'] ?? '',
                    'phone' => $data['user_phone'] ?? '',
                ];
            }

            $response = Http::withHeaders($this->headers())->post(
                "{$this->baseUrl}/checkout-invoice/create",
                $payload
            );

            $result = $response->json();

            if (($result['response_code'] ?? '') === '00') {
                return [
                    'success'     => true,
                    'token'       => $result['token'],
                    'payment_url' => $result['invoice_url'] ?? $result['url'] ?? '',
                ];
            }

            Log::error('Paydunya createInvoice failed', $result);
            return ['success' => false, 'error' => $result['response_text'] ?? 'Erreur Paydunya'];

        } catch (\Throwable $e) {
            Log::error('Paydunya exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyTransaction(string $token): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/checkout-invoice/confirm/{$token}");

            $result = $response->json();

            $status = match ($result['status'] ?? '') {
                'completed' => 'completed',
                'pending'   => 'pending',
                'cancelled' => 'cancelled',
                default     => 'failed',
            };

            return ['success' => true, 'status' => $status, 'data' => $result];

        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function parseWebhook(array $payload, string $rawBody, array $headers): ?array
    {
        $token  = $payload['data']['invoice']['token'] ?? null;
        $status = $payload['data']['invoice']['status'] ?? null;
        $amount = (int) ($payload['data']['invoice']['total_amount'] ?? 0);

        if (!$token || !$status) {
            return null;
        }

        return [
            'token'  => $token,
            'status' => $status === 'completed' ? 'completed' : 'failed',
            'amount' => $amount,
        ];
    }

    public function getPlanPrice(string $plan): int
    {
        $plans = AppConfig::get('app.premium_plans', []);
        return (int) ($plans[$plan]['price'] ?? 0);
    }

    private function headers(): array
    {
        return [
            'PAYDUNYA-MASTER-KEY'  => $this->masterKey,
            'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
            'PAYDUNYA-TOKEN'       => $this->token,
            'Content-Type'         => 'application/json',
        ];
    }
}
